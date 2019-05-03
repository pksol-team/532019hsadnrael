<?php

namespace Getresponse\WordPress\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class BaseTestCase
 * @package Getresponse\WordPress\Tests
 */
class BaseTestCase extends TestCase
{

    /**
     * @param string $className
     * @param array $methodsToOveride
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function createMockWithoutConstructor($className, $methodsToOveride = [])
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods($methodsToOveride)
            ->getMock();
    }
}