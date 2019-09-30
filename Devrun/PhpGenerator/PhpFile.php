<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    PhpFile.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\PhpGenerator;

use Nette\PhpGenerator\ClassType;
use Nette\Utils\Strings;

class PhpFile extends \Nette\PhpGenerator\PhpFile
{

    /** @var ClassType[] */
    private $classType = [];


    /**
     * @param  string|object
     *
     * @return ClassType
     */
    public function fromClass($class)
    {
        $classType = ClassType::from($class);
        $this->setClassMethodsBody($classType);
        $this->setClassConstants($classType);

        $newClass = $this->addClass($class)
            ->setExtends($classType->getExtends())
            ->setImplements($classType->getImplements())
            ->setAbstract($classType->isAbstract())
            ->setFinal($classType->isFinal())
            ->setProperties($classType->getProperties())
            ->setConstants($classType->getConstants())
            ->setTraits($this->getClassTraits($classType))
            ->setComment($this->getClassComment($classType))
            ->setMethods($this->getOnlyDirectClassMethods($classType));

        $this->setClassUses($newClass);
        $this->classType[$class] = $newClass;

        return $newClass;
    }


    public function getClassType($className)
    {
        if (!isset($this->classType[$className])) {
            $this->fromClass($className);
        }
        return $this->classType[$className];
    }



    /**
     * @param ClassType $classType
     *
     * @return array
     */
    private function getOnlyDirectClassMethods(ClassType $classType)
    {
        $className     = $this->getClassNamespaceName($classType);
        $classFilename = (new \ReflectionClass($className))->getFileName();

        $methods = [];
        foreach ($classType->getMethods() as $name => $method) {

            // method reflection info
            $reflectMethod = \Nette\Utils\Callback::toReflection([$className, $name]);

            // scan only this class methods, not parents
            if ($reflectMethod->getFileName() != $classFilename) continue;

            $methods[$name] = $method;
        }

        return $methods;
    }


    /**
     * @param ClassType $classType
     *
     * @return array
     */
    protected function getClassTraits(ClassType $classType)
    {
        $className   = $this->getClassNamespaceName($classType);
        $classTraits = (new \ReflectionClass($className))->getTraits();
        return array_keys($classTraits);
    }


    /**
     * @param ClassType $classType
     *
     * @return string
     */
    protected function getClassComment(ClassType $classType)
    {
        $className       = $this->getClassNamespaceName($classType);
        $classDocComment = (new \ReflectionClass($className))->getDocComment();

        $comment = "";
        foreach (preg_split("/(\r?\n)/", $classDocComment) as $line) {
            $line = Strings::replace($line, '%/\*\*[\s]*%');
            $line = Strings::replace($line, '%[\s]*\*\/%');
            $line = Strings::replace($line, '%[\s]*\*[\s]*%');
            $comment .= $line . PHP_EOL;
        }

        return trim($comment);
    }


    /**
     * load and set all methods body of the class
     *
     * @param ClassType $classType
     */
    protected function setClassMethodsBody(ClassType &$classType)
    {
        $className   = $this->getClassNamespaceName($classType);
        $fileContent = null;

        foreach ($this->getOnlyDirectClassMethods($classType) as $name => $method) {

//            dump($method);

            // method reflection info
            $reflectMethod = \Nette\Utils\Callback::toReflection([$className, $name]);

            // class file load
            if (null === $fileContent) $fileContent = file($reflectMethod->getFileName());

//            dump($fileContent);
//            dump($reflectMethod->getStartLine());
//            dump($reflectMethod->getEndLine());

            $methodBody = '';
            for ($i = $reflectMethod->getStartLine(); $i < $reflectMethod->getEndLine(); $i++) {
//                $methodBody .= trim($fileContent[$i]) . PHP_EOL;
            }

            $methodBody = str_replace('{', '', $methodBody);
            $methodBody = str_replace('}', '', $methodBody);
            $methodBody = trim($methodBody);

            $method->setBody($methodBody);
        }
    }

    /**
     * set class constants, this is not perfectly because there is not ideally annotations...  ( there is not constant comments etc. )
     *
     * @param ClassType $classType
     */
    protected function setClassConstants(ClassType &$classType)
    {
        $className       = $this->getClassNamespaceName($classType);
        $reflectionClass = new \ReflectionClass($className);
        $classConstants  = $reflectionClass->getConstants();

        // filter parentClass constants
        if ($parentClass = $reflectionClass->getParentClass()) {
            $parentClassConstants = $parentClass->getConstants();
            $classConstants       = array_diff_key($classConstants, $parentClassConstants);
        }

        // filter interfaces constants
        if ($interfaces = $reflectionClass->getInterfaces()) {
            foreach ($interfaces as $interface) {
                $classConstants = array_diff_key($classConstants, $interface->getConstants());
            }
        }

        foreach ($classConstants as $name => $value) {
            $classType->addConstant($name, $value);
        }
    }


    /**
     * set class uses
     *
     * @param ClassType $classType
     */
    protected function setClassUses(ClassType &$classType)
    {
        $className       = $this->getClassNamespaceName($classType);
        $reflectionClass = new \ReflectionClass($className);
        $fileContent     = file($reflectionClass->getFileName());

        // get array of use from line 1 to start class line
        $uses = array_map('trim', preg_grep('%use\s+.*%', array_slice($fileContent, 1, $reflectionClass->getStartLine() - 1)));

        foreach ($uses as $use) {
            if (preg_match('%use\s+(.*);%', $use, $matches)) {
                $classType->getNamespace()->addUse($matches[1]);
            }
        }
    }


    private function getClassNamespaceName(ClassType $classType)
    {
        return $classType->getNamespace()->getName() . '\\' . $classType->getName();
    }


}