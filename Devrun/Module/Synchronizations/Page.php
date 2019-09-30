<?php
/**
 * This file is part of souteze.pixman.cz.
 * Copyright (c) 2019
 *
 * @file    Page.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\Module\Synchronizations;

use Devrun\CmsModule\Entities\PageEntity;
use Devrun\CmsModule\Listeners\CreatePageResult;
use Devrun\CmsModule\Listeners\UpdatePageResult;
use Nette\Application\UI\Presenter;
use Nette\Reflection\ClassType;

class Page
{


    /**
     * synchronize all pages
     * for static page must exist template and presenter methods has no any parameter. Any un required parameter ?
     *
     * for dynamic page must exist template and presenter method [action|render| with any parameter. Can by defined @parent for parent page
     * example: @parent front:homepage:default
     * if not defined parent in annotation, synchronizationPages will call onCreatePage event
     *
     *
     * @param array $modules
     */
    public function synchronization(array $modules)
    {
        /** @var PageEntity[] $inPages */
        $inPages  = $this->entityManager->getRepository(PageEntity::getClassName())->findAll();

        /** @var PageEntity[] $dbPages */
        $dbPages  = [];
        $pageList = [];

        foreach ($inPages as $page) {
            $dbPages[(string)$page] = $page;
        }

//        dump($dbPages);
//        dump($modules);
//        die();


        foreach ($modules as $module => $presenters) {


            //            dump($this->moduleFacade->getModules());
            //            dump($modules);
//            die();
            //dump($presenters);
            //die();



            foreach ($presenters as $presenter => $templates) {

                $presenterReflections = [];

                foreach ($templates as $template => $templateInfo) {

                    $pageType = null;
                    $parentPageName = null;
                    $presenterClassName = $templateInfo['class'];

                    // create reflection of presenter
                    if (!isset($presenterReflections[$presenterClassName])) {

                        /** @var Presenter $presenterClass */
                        if ($presenterClass = $this->context->getByType($presenterClassName, false)) {
                            $presenterReflections[$presenterClassName] = [
                                'class'      => $presenterClass,
                                'reflection' => new ClassType($presenterClass)
                            ];

                        } else {
                            $presenterReflections[$presenterClassName] = null;
                        }
                    }

                    if ($presenterReflection = $presenterReflections[$presenterClassName]) {

                        $class = $presenterReflection['class'];

                        /** @var ClassType $reflection */
                        $reflection = $presenterReflection['reflection'];

                        if ($reflection->hasMethod($actionName = 'action' . ucfirst($template))) {
                            $refMethod = $reflection->getMethod($actionName);

                            if ($refMethod->getNumberOfParameters() > 0) {
                                $pageType = 'dynamic';

                                // parent page can write in annotation @parent(page name [front:homepage:default])
                                if ($refMethod->hasAnnotation('parent')) {
                                    $parentPageName = $refMethod->getAnnotation('parent');
                                }


//                                dump($presenterClassName);
//                                $classRef = ClassType::from($presenterClassName);
//                                dump($netteMethod = $classRef->getMethod($actionName));

//                                dump($netteMethod->getParameters());
//                                dump($netteMethod->getAnnotations());



//                                $reflection->getMethod($actionName)->;

//                                dump($refMethod->getReturnType());
//                                dump($refMethod->getModifiers());
//                                dump($refMethod->getParameters());
//                                dump($refMethod->getDocComment());
//                                dump($refMethod->getStaticVariables());
//                                dump($refMethod);


//                                die();

                            } else {
                                $pageType = 'static';
                            }

                        }

                        if ($pageType === null && $hasRender = $reflection->hasMethod($renderName = 'render' . ucfirst($template))) {
                            $refMethod = $reflection->getMethod($renderName);

                            if ($refMethod->getNumberOfParameters() > 0) {
                                $pageType = 'dynamic';

                                // parent page can write in annotation @parent(page name [front:homepage:default])
                                if ($refMethod->hasAnnotation('parent')) {
                                    $parentPageName = $refMethod->getAnnotation('parent');
                                }

                            } else {
                                $pageType = 'static';
                            }
                        }


                        /*
                        if (!$pageIsDynamic && $reflection->hasMethod($renderName = 'render' . ucfirst($template))) {
                            $refMethod = $reflection->getMethod($renderName);

                            if ($refMethod->getNumberOfParameters() > 0) {
                                $pageIsDynamic = true;

                                // parent page can write in annotation @parent(page name [front:homepage:default])
                                if ($refMethod->hasAnnotation('parent')) {
                                    $parentPageName = $refMethod->getAnnotation('parent');
                                }
                            }
                        }
                        */
                    }

                    if ($pageType) {
                        $pageName = strtolower($module) . ':' . strtolower($presenter) . ':' . strtolower($template);

                        $pageList[$pageName] = [
                            'pageType'       => $pageType,
                            'pageName'       => $pageName,
                            'parentPageName' => $parentPageName,
                            'module'         => $module,
                            'presenter'      => $presenter,
                            'action'         => $template,
                            'info'           => $templateInfo,
                        ];
                    }
                }
            }
        }

//        dump(array_keys($pageList));
//        dump(array_keys($dbPages));
//        die('END');



        /*
         * new pages
         */
        $newPages = array_diff(array_keys($pageList), array_keys($dbPages));

        $removePages = array_diff(array_keys($dbPages), array_keys($pageList));


//        dump($newPages);
//        dump($dbPages);
//        dump($pageList);
//        die("ASD");

        $updatePages = array_diff_ukey($pageList, $dbPages, function ($keyPage, $keyDbPage) use ($pageList, $dbPages, $newPages) {
//            dump($pageList[$keyPage]);
//            dump($dbPages[$keyDbPage]);

//            dump($keyPage);
//            dump($keyDbPage);

//            dump($pageList);

//            dump($newPages);
//            die();


            // if page for update is in new page list, this is equal [remove from update list]
            if (in_array($keyPage, $newPages)) {
                return 0;
            }

            $page   = isset($pageList[$keyPage]) ? $pageList[$keyPage] : null ;

            /** @var PageEntity $dbPage */
            $dbPage = isset($dbPages[$keyDbPage]) ? $dbPages[$keyDbPage] : null;

            if ($keyPage == $keyDbPage) {

                if ($dbPage->getType() == $page['pageType']) {
                    if ($dbPage->getType() == 'static' ) return 0;

                    // dynamic page must equal parent page yet
                    if ($parent = $dbPage->getParent()) {

                        if ($page['parentPageName']) {
                            return $page['parentPageName'] == $parent->getName() ? 0 : -1;
                        }

//                        return -1;

//                        dump($keyPage);
//                        dump($page);
//                        die();


                        $this->onAnnotationParentPage($keyPage, $resultEvent = new UpdatePageResult());

//                        dump($resultEvent);
//                        die();


                        return $resultEvent->getParentPageName() == $parent->getName() ? 0 : -1;
                    }

//                dump($dbPage);
//                dump($page);
//                die();


                }


//                return $dbPage->getType() == $page['pageType'] ? 0 : -1;
//                return $dbPage->getType() == $pageType[$page['pageIsDynamic']] ? 0 : -1;
            }

            if ($keyPage > $keyDbPage) {
//                dump($keyPage);
//                dump($keyDbPage);
                return -1;
            }

            return 1;

//            return $keyPage > $keyDbPage;

//            if ($page)

        });


//        dump($pageList);
//        die();


//        dump("new", $newPages);
//        dump("update", $updatePages);
//        dump("delete", $removePages);


        /*
         * if we have defined module default page, sort this default page name to first in list, me must insert firstly this default page
         */
        foreach ($modules as $module => $presenters) {
            if ($defaultPageName = $this->moduleFacade->getModules()[$module]->getDefaultPageName()) {
                if (($search = array_search($defaultPageName, $newPages)) !== false) {
                    unset($newPages[$search]);
                    array_unshift($newPages, $defaultPageName);
                }
            }
        }

//        dump("new", $newPages);
//        dump("update", $updatePages);
//        dump("delete", $removePages);



//        die();

        $pageEntities = $dbPages;

        $urlRoutes = $this->entityManager->createQueryBuilder()
            ->from(RouteTranslationEntity::class, 'e')
            ->select('e.url')
            ->getQuery()
            ->getResult();

        foreach ($newPages as $newPage) {
            $pageInfo = $pageList[$newPage];

//            dump($pageInfo);
//            die();



            $uri = ':' . ucfirst($pageInfo['module']) . ':' . $pageInfo['presenter'] . ':' . $pageInfo['action'];
            $url = Strings::webalize($pageInfo['presenter'] . '-' . $pageInfo['action']);
            $title = ucfirst($pageInfo['presenter']) . ' - ' . $pageInfo['action'];

            $inc = 1;
            $checkUrl = $url;
            while (in_array($checkUrl, $urlRoutes)) {
                $checkUrl = $url . "-" . $inc++;
            }

            $urlRoutes[] = $url = $checkUrl;

//            dump($url);
//            dump($uri);
//            dump($title);
//            dump($pageInfo);


            $pageEntity = (new PageEntity($pageInfo['module'], $pageInfo['presenter'], $pageInfo['action'], $this->translator))
                ->setClass($pageInfo['info']['class'])
                ->setFile($pageInfo['info']['template'])
                ->setType($pageInfo['pageType']);


            if ($pageInfo['pageType'] == 'static') {
                // static page
                $routeEntity = new RouteEntity($pageEntity, $this->translator);
                $routeEntity
                    ->setName($this->translator->translate('unknown page'))
                    ->setUrl($url)
                    ->setUri($uri)
                    ->setParams([])
                    ->setTitle($title);

                // jestliže máme nastaven defaultní presenter přímo v modulu, pak bude tato default page rodičem pro všechny presentery modulu
                if ($defaultPageName = $this->moduleFacade->getModules()[$pageInfo['module']]->getDefaultPageName()) {

                    // default page musí být známa
                    if (isset($pageEntities[$defaultPageName])) {
                        $pageEntity->setParent($pageEntities[$defaultPageName]);
                    }
                }
//                dump($defaultPageName);
//                dump($pageEntity);
//                die();



                $this->entityManager->persist($pageEntity)->persist($routeEntity);
                $pageEntity->mergeNewTranslations();
                $routeEntity->mergeNewTranslations();

            } else {
                // dynamic page
                if ($parentPageName = $pageInfo['parentPageName']) {
                    // find parent page from annotation
                    if ($parentPage = $this->entityManager->getRepository(PageEntity::class)->findOneBy(['name' => $parentPageName])) {
                        $pageEntity->setParent($parentPage);
                    }

//                    dump($parentPage);
                }

                if (!$pageEntity->getParent()) {
                    // parent page not found, find by callback
                    $this->onCreatePage($pageEntity, $createPageResult = new CreatePageResult());

                    if ($parentPage = $createPageResult->getParentPage()) {
                        $pageEntity->setParent($parentPage);
                    }


                }

//                dump($pageEntity);

                $this->entityManager->persist($pageEntity);

//                die("parent nalezen");

            }

            $pageEntities[(string)$pageEntity] = $pageEntity;

//            die("END");
        }

        foreach ($updatePages as $pageName => $updatePage) {
            // update page only for dynamic page
            continue;
//            dump($pageName);
//            dump($updatePage);
            if ($updatePage['pageType'] == 'dynamic') {

//                dump($dbPages);

                $pageEntity = $dbPages[$pageName];

                $parentPage = null;
                if ($parentPageName = $updatePage['parentPageName']) {
                    // find parent page from annotation
                    if ($parentPage = $this->entityManager->getRepository(PageEntity::class)->findOneBy(['name' => $parentPageName])) {

//                        dump($parentPage);
//                        die();


                        $pageEntity->setParent($parentPage);
                    }
                }
                if (!$parentPage) {
                    $this->onCreatePage($pageEntity, $createPageResult = new CreatePageResult());

                    if (!$parentPage = $createPageResult->getParentPage()) {
                        throw new PageNotFoundException("parent page \"$parentPageName\" not found in page \"{$updatePage['pageName']}\" ({$updatePage['info']['class']})");
                    }

                    $pageEntity->setParent($parentPage);
                }

                $pageEntity
                    ->setType($updatePage['pageType'])
                    ->setName($updatePage['pageName'])
                    ->setModule($updatePage['module'])
                    ->setPresenter($updatePage['presenter'])
                    ->setAction($updatePage['action'])
                    ->setClass($updatePage['info']['class'])
                    ->setFile($updatePage['info']['template']);
            }
        }


        /*
         * remove pages
         */
        $removePages = array_diff(array_keys($dbPages), array_keys($pageList));
        foreach ($removePages as $removePage) {
//            dump($removePages);
//            die();

            $this->entityManager->remove($dbPages[$removePage]);


        }



        if (!empty($newPages) || !empty($updatePages) || !empty($removePages)) {

//            die("ASd");

            $this->entityManager->flush();

//            dump($newPages);
//            dump($updatePages);
//
//            die("updated");
        }


    }



}