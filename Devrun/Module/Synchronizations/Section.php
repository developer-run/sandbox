<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    Section.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\Synchronizations;

use Devrun\CmsModule\Entities\PageEntity;
use Devrun\CmsModule\Entities\PageSectionsEntity;

class Section
{





    public function synchronization(array $modules)
    {
        $inSections = $this->entityManager->getRepository(PageSectionsEntity::getClassName())->findAll();

        $dbSections = [];
        foreach ($inSections as $section) {
            $dbSections[(string)$section] = $section;
        }

        $sectionList = [];
        foreach ($modules as $module => $presenters) {
            foreach ($presenters as $presenter => $templates) {

                foreach ($templates as $template => $templateInfo) {
                    $pageName    = strtolower($module) . ':' . strtolower($presenter) . ':' . strtolower($template);
                    $fileContent = file_get_contents($fileName = $templateInfo['realTemplatePath']);

                    if ($sections = $this->findSectionsInString($fileContent)) {
                        foreach ($sections as $section) {
                            $sectionName = "$pageName:$section";

                            $sectionList[$sectionName] = [
                                'module'    => $module,
                                'presenter' => $presenter,
                                'template'  => $template,
                                'page'      => $pageName,
                                'section'   => $section,
                                'info'      => $templateInfo,
                            ];
                        }
                    }
                }
            }
        }

        $pages = $this->entityManager->getRepository(PageEntity::getClassName())->findAssoc([], 'name');

        /*
         * new sections
         */
        $newSections = array_diff(array_keys($sectionList), array_keys($dbSections));
        foreach ($newSections as $newSection) {
            $pageName = $sectionList[$newSection]['page'];
            $page     = isset($pages[$pageName]) ? $pages[$pageName] : null;

            $entity = (new PageSectionsEntity())
                ->setPage($page)
                ->setName($sectionList[$newSection]['section']);

            $this->entityManager->persist($entity);
        }


        /*
         * remove sections
         */
        $removeSections = array_diff(array_keys($dbSections), array_keys($sectionList));
        foreach ($removeSections as $removeSection) {
            $this->entityManager->remove($dbSections[$removeSection]);
        }

        if (!empty($newSections) || !empty($removeSections)) {
            $this->entityManager->flush();
        }
    }




    /**
     * find sections in template string. Use macro section
     *
     * @example:
     *         {section sectionName}
     *         <h2>display</h2>
     *         {/section}
     *
     *         <div n:section="secondSection">
     *         <h3>title</h3>
     *         </div>
     *
     * @param $string
     *
     * @return array|bool
     */
    private function findSectionsInString($string)
    {
        if (preg_match_all('/({section\s*(?P<name>.*)\s*}|<div\s*n:section=\\"(?P<name2>.*)\\">)/', $string, $matches)) {
            $array1 = array_flip($matches['name']);
            $array2 = array_flip($matches['name2']);
            $sections = $array1 + $array2;
            unset($sections[""]);
            $sections = array_flip($sections);
            return $sections;
        }

        return false;
    }


}