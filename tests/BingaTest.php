<?php
namespace Moudarir\Binga\Test;

use PHPUnit\Framework\TestCase;

class BingaTest extends TestCase
{

    public function testRequest()
    {
        $this->AssertSame(1, 1, 'Same 1');
    }

    public function test__construct()
    {
        $this->AssertSame(1, 1, 'Same 2');
    }

    public function testGenerateCheckSum()
    {
        $this->AssertSame(1, 2, 'Same 3');
    }

    public function testSetExpirationDate()
    {
        $this->AssertSame(1, 1, 'Same 4');
    }
}
