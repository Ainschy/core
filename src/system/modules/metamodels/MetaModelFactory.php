<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * This is the MetaModel factory interface.
 * To create a MetaModel instance, either call @link{MetaModelFactory::byId()} or @link{MetaModelFactory::byTableName()}
 * 
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
// TODO: check if extending System is really neccessary.
class MetaModelFactory /*extends System*/ implements IMetaModelFactory
{
	/**
	 * All MetaModel instances.
	 * Assiciation: id => object
	 * 
	 * @var array
	 */
	protected static $arrInstances = array();

	/**
	 * Returns the proper user object for the current context.
	 * 
	 * @return BackendUser|FrontendUser|null the BackendUser when TL_MODE == 'BE', the FrontendUser when TL_MODE == 'FE' or null otherwise
	 */
	protected static function getUser()
	{
		if(TL_MODE=='BE')
		{
			return BackendUser::getInstance();
		} else if(TL_MODE=='FE')
		{
			return FrontendUser::getInstance();
		}
		return null;
	}

	/**
	 * This initializes the Contao Singleton object stack as it must be, 
	 * when using singletons within the config.php file of an Extension.
	 * 
	 * @return void
	 */
	protected static function initializeContaoObjectStack()
	{
		// all of these getInstance calls are neccessary to keep the instance stack intact 
		// and therefore prevent an Exception in unknown on line 0.
		// Hopefully this will get fixed with Contao Reloaded or Contao 3.
		Config::getInstance();
		Environment::getInstance();
		Input::getInstance();

		// request token became available in 2.11
		if (version_compare(TL_VERSION, '2.11', '>='))
		{
			RequestToken::getInstance();
		}

		self::getUser();

		Database::getInstance();
	}

	/**
	 * Create a MetaModel instance with the given information.
	 * 
	 * @param array $arrData the meta information for the MetaModel.
	 * 
	 * @return void
	 */
	protected static function createInstance($arrData)
	{
		$objMetaModel = null;
		if ($arrData)
		{
			// NOTE: we might want to add support for a model type here in the future, where the model 
			// can transport it's own class or even factory within a lookup table. This will allow
			// other devs to inherit from MetaModel but perform different tasks.
			$objMetaModel = new MetaModel($arrData);
			self::$arrInstances[$arrData['id']] = $objMetaModel;
		}
		return $objMetaModel;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function byId($intId)
	{
		if (array_key_exists($intId, self::$arrInstances))
		{
			return self::$arrInstances[$intId];
		}
		$objData = Database::getInstance()->prepare('SELECT * FROM tl_metamodel WHERE id=?')
										->limit(1)
										->execute($intId);
		return ($objData->numRows)?self::createInstance($objData->row()):null;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function byTableName($strTablename)
	{
		$objData = Database::getInstance()->prepare('SELECT * FROM tl_metamodel WHERE tableName=?')
										->limit(1)
										->execute($strTablename);
		if (array_key_exists($objData->id, self::$arrInstances))
		{
			return self::$arrInstances[$objData->id];
		}
		return ($objData->numRows)?self::createInstance($objData->row()):null;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getAllTables()
	{
		self::initializeContaoObjectStack();

		$objDB = Database::getInstance();
		if($objDB)
		{
			if (!$objDB->tableExists('tl_metamodel'))
			{
				// I can't work without a properly installed database.
				return array();
			}
			return $objDB->execute('SELECT * FROM tl_metamodel')
						->fetchEach('tableName');
		}
	}

	/**
	 * Currently proof of concept, we will rather build a new management interface for this functionality.
	 * 
	 * @return void
	 */
	public static function buildBackendMenu()
	{
		self::initializeContaoObjectStack();

		$GLOBALS['TL_CSS'][] = 'system/modules/metamodels/html/style.css'; 

		$GLOBALS['BE_MOD']['system']['metamodel'] = array
		(
			'tables'			=> array_merge(array('tl_metamodel', 'tl_metamodel_attribute'), self::getAllTables()),
			'icon'				=> 'system/modules/metamodels/html/icon.gif',
		);

		$objDB = Database::getInstance();
		if ($objDB)
		{
			if (!$objDB->tableExists('tl_metamodel'))
			{
				// I can't work without a properly installed database.
				return;
			}
			$objUser = self::getUser();

			// work around as the TL_PATH constant is set after this routine has been run.
			// if this is not in place, BackendUser::authenticate() will redirect us to http://domain.tldtl_path/contao/index.php
			// if no user is properly logged in.
			// We also have to fix up the "script" parameter, as this one will otherwise try to redirect from "contao/index.php" to "/contao/index.php"
			// therefore creating an infinite redirect loop.
			$Env = Environment::getInstance();
			$Env->base = $Env->url . $GLOBALS['TL_CONFIG']['websitePath'] . '/';
			$Env->script = preg_replace('/^' . preg_quote($GLOBALS['TL_CONFIG']['websitePath'], '/') . '\/?/i', '', $Env->scriptName);

			// TODO: double, triple and quadro check that this is really safe context here.
			$objUser->authenticate();
			// restore initial settings.
			$Env->base = null;
			$Env->script = null;

			$arrMetaModels = array();
			$objMetaModels = $objDB->execute('SELECT * FROM tl_metamodel');
			while ($objMetaModels->next())
			{
				if (!$objMetaModels->backendsection)
				{
					continue;
				}

				if (!MetaModelPermissions::hasUserAccessTo($objUser, $objMetaModels->id))
				{
					continue;
				}

				$strModuleName = 'metamodel_' . $objMetaModels->tableName;
				$strTableCaption = $objMetaModels->name;

				// keep backend section handy.
				$arrMetaModels[$objMetaModels->tableName] = $objMetaModels->backendsection;

				// determine image to use.
				if ($objMetaModels->backendicon && file_exists(TL_ROOT . '/' . $objMetaModels->backendicon))
				{
					$strIcon = $objMetaModels->backendicon;
				} else {
					$strIcon = 'system/modules/metamodels/html/icon.gif';
				}

				$GLOBALS['BE_MOD'][$objMetaModels->backendsection][$strModuleName] = array
				(
					'tables'			=> array($objMetaModels->tableName),
					'icon'				=> $strIcon,
				);
				$GLOBALS['TL_LANG']['MOD'][$strModuleName] = array($strTableCaption);

				if ($objMetaModels->ptable)
				{
					$GLOBALS['BE_MOD'][$arrMetaModels[$objMetaModels->ptable]]['metamodel_' . $objMetaModels->ptable]['tables'][] = $objMetaModels->tableName;
				}
			}
		}
	}
}

?>