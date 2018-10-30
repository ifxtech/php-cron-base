<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 24.10.18
 * Time: 16:24
 */

namespace szczad;


use DateTime;
use PHPUnit\Framework\TestCase;
use szczad\schedule\CrontabPeriodicSchedule;

class CrontabPeriodicScheduleTest extends TestCase {
    private $object;

    protected function setUp() {
        parent::setUp();

        $this->object = new CrontabPeriodicSchedule("empty command");
    }

    protected function tearDown() {
        parent::tearDown();

        $this->object = null;
    }

    private static function getClass() {
        return new \ReflectionClass(CrontabPeriodicSchedule::class);
    }


    private static function getMethod($name) {
        $class = self::getClass();
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    private static function getProperty($name) {
        $class = self::getClass();
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * CrontabPeriodicSchedule::getAfter($field, $value, $range_start, $range_end, &$add = 0);
     */
    public function test_getAfter_inFullRange() {
        $method = self::getMethod('getAfter');

        $value = 2;
        $field = [0, 1, 2, 3, 4];
        $range_start = 0;
        $range_end = 4;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(2, $result);
        self::assertEquals($carry,0);
    }

    public function test_getAfter_inPartialRange_openRight() {
        $method = self::getMethod('getAfter');

        $value = 2;
        $field = [0, 2, 3];
        $range_start = 0;
        $range_end = 4;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(2, $result);
        self::assertEquals($carry,0);
    }

    public function test_getAfter_inPartialRange_openLeft() {
        $method = self::getMethod('getAfter');

        $value = 2;
        $field = [1, 2, 3, 4];
        $range_start = 0;
        $range_end = 4;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(2, $result);
        self::assertEquals(0, $carry);
    }

    public function test_getAfter_inPartialRange_open() {
        $method = self::getMethod('getAfter');

        $value = 2;
        $field = [1, 2, 3];
        $range_start = 0;
        $range_end = 4;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(2, $result);
        self::assertEquals(0, $carry);
    }

    public function test_getAfter_notInPartialRange() {
        $method = self::getMethod('getAfter');

        $value = 2;
        $field = [0, 1, 3, 4];
        $range_start = 0;
        $range_end = 4;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(3, $result);
        self::assertEquals(0, $carry);
    }

    public function test_getAfter_notInPartialRange_openRight() {
        $method = self::getMethod('getAfter');

        $value = 2;
        $field = [0, 1,];
        $range_start = 0;
        $range_end = 4;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(0, $result);
        self::assertEquals(1, $carry);
    }

    public function test_getAfter_notInPartialRange_openRightPlus() {
        $method = self::getMethod('getAfter');

        $value = 3;
        $field = [1, 2];
        $range_start = 1;
        $range_end = 4;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(1, $result);
        self::assertEquals(1, $carry);
    }

    public function test_getAfter_notInLargeRange_openRightPlus() {
        $method = self::getMethod('getAfter');

        $value = 13;
        $field = [15, 16];
        $range_start = 13;
        $range_end = 17;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(15, $result);
        self::assertEquals(0, $carry);
    }

    public function test_getAfter_OutOfRange_empty() {
        $method = self::getMethod('getAfter');

        $value = 11;
        $field = [];
        $range_start = 13;
        $range_end = 17;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(-1, $result);
        self::assertEquals(-1, $carry);
    }

    public function test_getAfter_OutOfRange_left() {
        $method = self::getMethod('getAfter');

        $value = 11;
        $field = [15, 16];
        $range_start = 13;
        $range_end = 17;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(15, $result);
        self::assertEquals(0, $carry);
    }

    public function test_getAfter_OutOfRange_right() {
        $method = self::getMethod('getAfter');

        $value = 21;
        $field = [15, 16];
        $range_start = 13;
        $range_end = 17;
        $carry = -1;

        $result = $method->invokeArgs($this->object, [$field,$value, $range_start, $range_end, &$carry]);

        self::assertEquals(15, $result);
        self::assertEquals(1, $carry);
    }

    /**
     * CrontabPeriodicSchedule::getDayOfWeekFromDateTime($datetime);
     */
    public function test_getDayOfWeekFromDateTime() {
        $method = self::getMethod('getDayOfWeekFromDateTime');

        $given = new DateTime("2015-02-13 11:33:22");
        $then = 5; // Friday

        $when = $method->invokeArgs($this->object, [$given]);

        self::assertEquals($then, $when);
    }

    /**
     * CrontabPeriodicSchedule::getDayOfWeekFromDateTime($datetime);
     */
    public function test_getMonthFromDateTime() {
        $method = self::getMethod('getMonthFromDateTime');

        $given = new DateTime("2015-02-13 11:33:22");
        $then = 2;

        $when = $method->invokeArgs($this->object, [$given]);

        self::assertEquals($then, $when);
    }

    /**
     * CrontabPeriodicSchedule::getDayOfWeekFromDateTime($datetime);
     */
    public function test_getDayFromDateTime() {
        $method = self::getMethod('getDayFromDateTime');

        $given = new DateTime("2015-02-13 11:33:22");
        $then = 13;

        $when = $method->invokeArgs($this->object, [$given]);

        self::assertEquals($then, $when);
    }

    /**
     * CrontabPeriodicSchedule::setField($name, $value);
     */
    public function test_setField() {
        $method = self::getMethod('setField');

        $given_array = [1, 2, 3];
        $given_field = [];
        $then = [1, 2, 3];

        $method->invokeArgs($this->object, [&$given_field, $given_array]);

        self::assertTrue(is_array($given_field));
        self::assertEquals($then, $given_field);
    }

}
