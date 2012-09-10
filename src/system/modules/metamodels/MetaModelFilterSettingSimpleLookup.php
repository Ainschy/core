<?php

class MetaModelFilterSettingSimpleLookup extends MetaModelFilterSetting
{
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
		if ($objAttribute)
		{
			$arrMyFilterUrl = array_slice($arrFilterUrl, 0);
			if ($this->get('urlparam') && $arrFilterUrl[$this->get('urlparam')])
			{
				$arrMyFilterUrl[$objAttribute->getColName()] = $arrFilterUrl[$this->get('urlparam')];
			}
			// call prepare now.
			$objFilterRule = $objAttribute->parseFilterUrl($arrMyFilterUrl);
			if ($objFilterRule)
			{
				$objFilter->addFilterRule($objFilterRule);
			}
		}
	}

	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting)
	{
		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
		if ($objAttribute)
		{
			// TODO: shall we omit returning of empty values?
			$arrResult = $objItem->parseAttribute($objAttribute->getColName(), 'text', $objRenderSetting);
			return array(($this->get('urlparam')?$this->get('urlparam'):$objAttribute->getColName()) => urlencode($arrResult['text']));
		}
	}
}

?>