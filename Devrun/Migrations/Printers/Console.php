<?php


namespace Devrun\Migrations\Printers;

class Console extends \Nextras\Migrations\Printers\Console
{

    public function printIntro($mode)
    {
        $this->output('Devrun Migrations');
        $this->output(strtoupper($mode), self::COLOR_INTRO);
    }


}