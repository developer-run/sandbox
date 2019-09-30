<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2017
 *
 * @file    PhpNamespace.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\PhpGenerator;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Helpers;
use Nette\Utils\Strings;

class PhpNamespace extends \Nette\PhpGenerator\PhpNamespace
{

    /** @var ClassType[] */
    private $classes = [];

    /** @var string[] */
    protected $uses = [];



    public function addClassType(ClassType $classType)
    {
        if (!isset($this->classes[$name = $classType->getName()])) {
            $this->addUse($name . '\\' . $name);
            $this->classes[$name] = $classType;




        }
        return $this->classes[$name];
    }




    /**
     * @return string PHP code
     */
    public function __toString()
    {
        $uses = [];
        asort($this->uses);
        foreach ($this->uses as $alias => $name) {
            $useNamespace = Helpers::extractNamespace($name);

            if ($this->getName() !== $useNamespace) {
                if ($alias === $name || substr($name, -(strlen($alias) + 1)) === '\\' . $alias) {
                    $uses[] = "use {$name};";
                } else {
                    $uses[] = "use {$name} as {$alias};";
                }
            }
        }

        $body = ($uses ? implode("\n", $uses) . "\n\n" : '')
            . implode("\n", $this->classes);

        if ($this->getBracketedSyntax()) {
            return 'namespace' . ($this->getName() ? ' ' . $this->getName() : '') . " {\n\n"
                . Strings::indent($body)
                . "\n}\n";

        } else {
            return ($this->getName() ? "namespace {$this->getName()};\n\n" : '')
                . $body;
        }
    }

}