<?php

declare(strict_types=1);

namespace viavario\rizivclient\tests;

use PHPUnit\Framework\TestCase;
use viavario\rizivclient\RizivResult;

/**
 * Unit tests for the {@see \viavario\rizivclient\RizivResult} data object.
 */
class RizivResultTest extends TestCase
{
    /**
     * Verifies the constructor assigns values and toArray() returns the expected map.
     */
    public function testConstructorAndToArray(): void
    {
        $name = 'John Doe';
        $riziv_number = '731106598';
        $profession = 'Doctor';
        $contracted = true;
        $qualification = 'MD';
        $qualification_date = new \DateTime('2020-01-01');

        $result = new RizivResult($name, $riziv_number, $profession, $contracted, $qualification, $qualification_date);

        $this->assertEquals($name, $result->name);
        $this->assertEquals($riziv_number, $result->riziv_number);
        $this->assertEquals($profession, $result->profession);
        $this->assertEquals($contracted, $result->contracted);
        $this->assertEquals($qualification, $result->qualification);
        $this->assertEquals($qualification_date, $result->qualification_date);

        $expectedArray = [
            'name' => $name,
            'riziv_number' => $riziv_number,
            'profession' => $profession,
            'contracted' => $contracted,
            'qualification' => $qualification,
            'qualification_date' => $qualification_date,
        ];

        $this->assertEquals($expectedArray, $result->toArray());
    }
}