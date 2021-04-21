<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Symfony\Component\Finder\Finder;

/**
 * This migration find own clear all template files (mm_filter_clearall*.*) and write a notice.
 */
class FindClearAllTemplateMigration extends AbstractMigration
{
    /**
     * @var string
     */
    private $projectDir;

    /**
     * FindClearAllTemplateMigration constructor.
     *
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Detect old style named MetaModels filter template files (mm_filter_clearall*.*).';
    }

    /**
     * Must only run if:
     * - we find own clear all template files (mm_filter_clearall*.*).
     *
     * @return bool
     */
    public function shouldRun(): bool
    {
        if ($this->findClearAllTemplates()) {
            return true;
        }

        return false;
    }

    /**
     * Search own clear all template files (mm_filter_clearall*.*).
     *
     * @return MigrationResult
     */
    public function run(): MigrationResult
    {
        if ($this->findClearAllTemplates()) {
            return new MigrationResult(
                false,
                'Old style named template files "mm_filter_clearall*.*" found - please rename to "mm_clearall*.html5" and select in module and content elements. This CAN NOT be done automatically!'
            );
        }
    }

    /**
     * Find own clear all template files (mm_filter_clearall*.*).
     *
     * @return bool
     */
    private function findClearAllTemplates(): bool
    {
        $files = Finder::create()->in($this->projectDir . '/templates')->name('mm_filter_clearall*.*');

        if (!empty($files)) {
            return true;
        }

        return false;
    }
}
