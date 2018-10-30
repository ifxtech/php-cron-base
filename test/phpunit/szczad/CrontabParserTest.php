<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 24.10.18
 * Time: 16:24
 */

namespace szczad;


use PHPUnit\Framework\TestCase;
use szczad\exceptions\InvalidPeriodValueException;
use szczad\exceptions\UnknownNamedPeriodException;
use szczad\schedule\CrontabPeriodicSchedule;
use szczad\parser\CrontabParser;
use szczad\schedule\Schedule;

class CrontabParserTest extends TestCase {
    private $object;
    private $months = [
    'JAN' => 1,
    'FEB' => 2,
    'MAR' => 3,
    'APR' => 4,
    'MAY' => 5,
    'JUN' => 6,
    'JUL' => 7,
    'AUG' => 8,
    'SEP' => 9,
    'OCT' => 10,
    'NOV' => 11,
    'DEC' => 12
    ];

    protected function setUp() {
        parent::setUp();

        $this->object = new CrontabParser(null, null);
    }

    protected function tearDown() {
        parent::tearDown();

        $this->object = null;
    }


    private static function getMethodAsPublic($name) {
        $class = new \ReflectionClass(CrontabParser::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * CrontabParser::getSchedule(&$line);
     */
    public function test_getSchedule_execution() {
        $line_before = "@named /path/command";
        $line_after = "1 2 3 4 5 6 /path/command";
        $num = 6;

        $mock = $this->getMockBuilder(CrontabParser::class)
            ->setConstructorArgs([null, CrontabPeriodicSchedule::class])
            ->setMethods(['fixLine', 'lineToConstructorArgs'])
            ->getMock();

        $mock->expects($this->once())
            ->method('fixLine')
            ->with(
                $this->equalTo($line_before)
            )
            ->willReturnCallback(function (&$line) {
                $line = "1 2 3 4 5 6 /path/command";
                return 6;
            });

        $mock->expects($this->once())
            ->method('lineToConstructorArgs')
            ->with(
                $this->equalTo($line_after),
                $this->equalTo($num)
            )
            ->willReturn(["/path/command", [1], [2], [3], [4], [5], [6]]);

        $result = $mock->getSchedule($line_before);

        self::assertInstanceOf(Schedule::class, $result);
    }

    /**
     * CrontabParser::getSchedule(&$line);
     */
    public function test_getSchedule_invalid_named() {
        $given = "@named /path/command";

        $when = $this->object->getSchedule($given);
        self::assertNull($when);
    }

    /**
     * CrontabParser::getSchedule(&$line);
     */
    public function test_getSchedule_invalid_extended() {
        $given = "+named /path/command";

        $when = $this->object->getSchedule($given);
        self::assertNull($when);
    }

    /**
     * CrontabParser::fixline(&$line);
     */
    public function test_fixline_pass_1() {
        $given = "@daily /path/command";
        $then = '0 0 0 * * * /path/command';

        $when = $this->object->fixLine($given);

        self::assertEquals($then, $given);
        self::assertEquals(6, $when);
    }

    public function test_fixline_pass_2() {
        $method = self::getMethodAsPublic('fixLine');

        $given = "+0 1 2 3 4 5 6 /path/command";
        $then = '0 1 2 3 4 5 6 /path/command';

        $when = $method->invokeArgs($this->object, [&$given]);

        self::assertEquals($then, $given);
        self::assertEquals(6, $when);
    }

    /**
     * CrontabParser::lineToConstructorArgs($line, num);
     */
    public function test_lineToConstructorArgs_pass_1() {
        $given_line= "1-2 2 3 4 5 /path/command";
        $given_num = 5;

        $mock = $this->getMockBuilder(CrontabParser::class)
            ->setConstructorArgs([null, CrontabPeriodicSchedule::class])
            ->setMethods(['parseField'])
            ->getMock();

        $mock->expects($this->atLeast(5))
            ->method('parseField')
            ->withConsecutive(
                [$this->anything(), $this->equalTo(0), $this->equalTo(7), $this->equalTo(CrontabParser::DAY_OF_WEEK)],
                [$this->anything(), $this->equalTo(1), $this->equalTo(12), $this->equalTo(CrontabParser::MONTH)],
                [$this->anything(), $this->equalTo(1), $this->equalTo(31)],
                [$this->anything(), $this->equalTo(0), $this->equalTo(23)],
                [$this->anything(), $this->equalTo(0), $this->equalTo(59)]
            )
            ->willReturnCallback(function($field, $start, $end, $mapping = []){
                return [$end];
            });

        $when = $mock->lineToConstructorArgs($given_line, $given_num);

        self::assertEquals(['/path/command', [7], [12], [31], [23], [59], [0]], $when);
    }

    /**
     * CrontabParser::lineToConstructorArgs($line, num);
     */
    public function test_lineToConstructorArgs_pass_2() {
        $given_line= "1-2 2 3 4 5 6 /path/command";
        $given_num = 6;

        $mock = $this->getMockBuilder(CrontabParser::class)
            ->setConstructorArgs([null, CrontabPeriodicSchedule::class])
            ->setMethods(['parseField'])
            ->getMock();

        $mock->expects($this->atLeast(5))
            ->method('parseField')
            ->withConsecutive(
                [$this->anything(), $this->equalTo(0), $this->equalTo(7), $this->equalTo(CrontabParser::DAY_OF_WEEK)],
                [$this->anything(), $this->equalTo(1), $this->equalTo(12), $this->equalTo(CrontabParser::MONTH)],
                [$this->anything(), $this->equalTo(1), $this->equalTo(31)],
                [$this->anything(), $this->equalTo(0), $this->equalTo(23)],
                [$this->anything(), $this->equalTo(0), $this->equalTo(59)],
                [$this->anything(), $this->equalTo(0), $this->equalTo(59)]
            )
            ->willReturnCallback(function($field, $start, $end, $mapping = []){
                return [$end];
            });

        $when = $mock->lineToConstructorArgs($given_line, $given_num);

        self::assertEquals(['/path/command', [7], [12], [31], [23], [59], [59]], $when);
    }

    /**
     * CrontabParser::namedToPeriod($value);
     */
    public function test_namedToPeriod_pass_1() {
        $given = "@daily /path/command";
        $then = "0 0 0 * * * /path/command";
        $this->object->namedToPeriod($given);

        self::assertEquals($then, $given);
    }

    public function test_namedToPeriod_pass_2() {
        $given = "@invalid /path/command";

        self::expectException(UnknownNamedPeriodException::class);
        $this->object->namedToPeriod($given);
    }

    public function test_namedToPeriod_reboot_generator() {
        $given = "@reboot /path/command";
        $then = "13 03 13 14 2 * /path/command";

        $new_time = mktime(13, 03, 12, 02, 14, 2016);
        timecop_freeze($new_time);
        $this->object->namedToPeriod($given);
        timecop_return();

        self::assertEquals($then, $given);
    }


    /**
     * CrontabParser::getParts($value);
     */
    public function test_getParts_pass1() {
        $method = self::getMethodAsPublic('getParts');

        $parts = $method->invokeArgs($this->object, ["12,32/12,24-53/15"]);
        self::assertEquals(["12", "32/12", "24-53/15"], $parts);
    }

    public function test_getParts_pass2() {
        $method = self::getMethodAsPublic('getParts');

        $parts = $method->invokeArgs($this->object, ["*,12,32/12,24-53/15"]);
        self::assertEquals(["*", "12", "32/12", "24-53/15"], $parts);
    }

    public function test_getParts_pass3() {
        $method = self::getMethodAsPublic('getParts');

        $parts = $method->invokeArgs($this->object, [",12,32/12,24-53/15"]);
        self::assertEquals(["", "12", "32/12", "24-53/15"], $parts);
    }

    /**
     * CrontabParser::checkPart($value);
     */
    public function testCheckPartPass1() {
        $method = self::getMethodAsPublic('checkPart');

        $thrown = False;
        try {
            $method->invokeArgs($this->object, ["12"]);
        } catch (\Exception $e) {
            $thrown = True;
        }

        self::assertFalse($thrown);
    }

    public function testCheckPartPass2() {
        $method = self::getMethodAsPublic('checkPart');

        $this->expectException(InvalidPeriodValueException::class);
        $method->invokeArgs($this->object, [""]);
    }

    /**
     * CrontabParser::getStep(&$part, &$step);
     */
    public function test_getStep_no_step() {
        $method = self::getMethodAsPublic('getStep');

        $part = "12";
        $step = -1;

        $method->invokeArgs($this->object, [&$part, &$step]);

        self::assertEquals("12", $part);
        self::assertEquals(1, $step);
    }

    public function test_getStep_no_step_value() {
        $method = self::getMethodAsPublic('getStep');

        $part = "12/";
        $step = -1;

        self::expectException(InvalidPeriodValueException::class);
        $method->invokeArgs($this->object, [&$part, &$step]);

        self::assertEquals("12/", $part);
        self::assertEquals(1, $step);
    }

    public function test_getStep_valid_2() {
        $method = self::getMethodAsPublic('getStep');

        $part = "12/1";
        $step = -1;

        $method->invokeArgs($this->object, [&$part, &$step]);

        self::assertEquals("12", $part);
        self::assertEquals(1, $step);
    }

    public function test_getStep_valid_3() {
        $method = self::getMethodAsPublic('getStep');

        $part = "12/4";
        $step = -1;

        $method->invokeArgs($this->object, [&$part, &$step]);

        self::assertEquals("12", $part);
        self::assertEquals(4, $step);
    }

    public function test_getStep_invalidStep() {
        $method = self::getMethodAsPublic('getStep');

        $part = "12/4/2";
        $step = -1;

        self::expectException(InvalidPeriodValueException::class);
        $method->invokeArgs($this->object, [&$part, &$step]);

        self::assertEquals("12/4/2", $part);
        self::assertEquals(4, $step);
    }

    public function test_getStep_negativeStep() {
        $method = self::getMethodAsPublic('getStep');

        $part = "12/-5";
        $step = -1;

        self::expectException(InvalidPeriodValueException::class);
        $method->invokeArgs($this->object, [&$part, &$step]);

        self::assertEquals("12/-5", $part);
        self::assertEquals(-1, $step);
    }

    /**
     * CrontabParser::getRange(&$part, &$step, &$right);
     */
    public function test_getRange_no_range_no_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "12";
        $left = -5;
        $right = -3;

        $method->invokeArgs($this->object, [&$part, &$left, &$right]);

        self::assertEquals(12, $left);
        self::assertEquals(12, $right);
    }

    public function test_getRange_1_element_range_no_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "12-12";
        $left = -5;
        $right = -3;

        $method->invokeArgs($this->object, [$part, &$left, &$right]);

        self::assertEquals(12, $left);
        self::assertEquals(12, $right);
    }

    public function test_getRange_range_no_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "10-14";
        $left = -5;
        $right = -3;

        $method->invokeArgs($this->object, [$part, &$left, &$right]);

        self::assertEquals(10, $left);
        self::assertEquals(14, $right);
    }

    public function test_getRange_reversedRange_no_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "14-10";
        $left = -5;
        $right = -3;

        $method->invokeArgs($this->object, [$part, &$left, &$right]);

        self::assertEquals(14, $left);
        self::assertEquals(10, $right);
    }

    public function test_getRange_invalidRange_no_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "14-10-12";
        $left = -5;
        $right = -3;

        self::expectException(InvalidPeriodValueException::class);
        $method->invokeArgs($this->object, [&$part, &$left, &$right]);

        self::assertEquals(-5, $left);
        self::assertEquals(-3, $right);
    }

    public function test_getRange_invalidRange2_no_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "14-10-";
        $left = -5;
        $right = -3;

        self::expectException(InvalidPeriodValueException::class);
        $method->invokeArgs($this->object, [&$part, &$left, &$right]);

        self::assertEquals(-5, $left);
        self::assertEquals(-3, $right);
    }

    public function test_getRange_validRange_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "JAN-DEC";
        $left = -5;
        $right = -3;

        $method->invokeArgs($this->object, [&$part, &$left, &$right, $this->months]);

        self::assertEquals(1, $left);
        self::assertEquals(12, $right);
    }

    public function test_getRange_invalidRange_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "JAN-NONE";
        $left = -5;
        $right = -3;

        self::expectException(InvalidPeriodValueException::class);
        $method->invokeArgs($this->object, [&$part, &$left, &$right, $this->months]);

        self::assertEquals(-5, $left);
        self::assertEquals(-3, $right);
    }

    public function test_getRange_validRange_with_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "5-12";
        $left = -5;
        $right = -3;

        $method->invokeArgs($this->object, [&$part, &$left, &$right, $this->months]);

        self::assertEquals(5, $left);
        self::assertEquals(12, $right);
    }

    public function test_getRange_reversedRange_mapping() {
        $method = self::getMethodAsPublic('getRange');

        $part = "MAY-JAN";
        $left = -5;
        $right = -3;

        $method->invokeArgs($this->object, [&$part, &$left, &$right, $this->months]);

        self::assertEquals(5, $left);
        self::assertEquals(1, $right);
    }

    /**
     * CrontabParser::populateResults(&$result, $left, $right, $step);
     */
    public function test_populateResults_valid_single() {
        $method = self::getMethodAsPublic('populateResults');

        $array = [];
        $left = 2;
        $right = 5;
        $step = 1;

        $method->invokeArgs($this->object, [&$array, &$left, &$right, $step]);

        self::assertEquals([2, 3, 4, 5], $array);
    }

    public function test_populateResults_valid_large_step() {
        $method = self::getMethodAsPublic('populateResults');

        $array = [];
        $left = 2;
        $right = 4;
        $step = 2;

        $method->invokeArgs($this->object, [&$array, &$left, &$right, $step]);

        self::assertEquals([2, 4], $array);
    }


    public function test_populateResults_very_large_range_with_step() {
        $method = self::getMethodAsPublic('populateResults');

        $array = [];
        $left = 2;
        $right = 14;
        $step = 6;

        $method->invokeArgs($this->object, [&$array, &$left, &$right, $step]);

        self::assertEquals([6, 12], $array);
    }

    public function test_populateResults_reverseRange() {
        $method = self::getMethodAsPublic('populateResults');

        $array = [];
        $left = 5;
        $right = 2;
        $step = 2;

        $method->invokeArgs($this->object, [&$array, &$left, &$right, $step]);

        self::assertEquals([], $array);
    }

    public function test_populateResults_negativeStep() {
        $method = self::getMethodAsPublic('populateResults');

        $array = null;
        $left = 5;
        $right = 2;
        $step = -1;

        self::expectException(\InvalidArgumentException::class);
        $method->invokeArgs($this->object, [&$array, &$left, &$right, $step]);

        self::assertEquals(null, $array);
    }

    /**
     * CrontabParser::cleanupRange($array);
     */
    public function test_cleanupRange_pass_1() {
        $method = self::getMethodAsPublic('cleanupRange');

        $array      = [1, 2, 3, 4, 5];
        $expected   = [1, 2, 3, 4, 5];
        $result = $method->invokeArgs($this->object, [$array]);

        self:self::assertEquals($expected, $result);
    }

    public function test_cleanupRange_pass_2() {
        $method = self::getMethodAsPublic('cleanupRange');

        $array      = [1, 5, 3, 4, 2];
        $expected   = [1, 2, 3, 4, 5];
        $result = $method->invokeArgs($this->object, [$array]);

        self:self::assertEquals($expected, $result);
    }

    public function test_cleanupRange_pass_3() {
        $method = self::getMethodAsPublic('cleanupRange');

        $array      = [1, 5, 3, 5, 2];
        $expected   = [1, 2, 3, 5];
        $result = $method->invokeArgs($this->object, [$array]);

        self:self::assertEquals($expected, $result);
    }

    /**
     * CrontabParser::getMappedInteger($value, $mapping);
     */
    public function test_getMappedInteger_valid_mapping() {
        $method = self::getMethodAsPublic('getMappedInteger');

        $value = "MAY";
        $result = $method->invokeArgs($this->object, [$value, $this->months]);

        self:self::assertEquals(5, $result);
    }

    public function test_getMappedInteger_invalid_mapping() {
        $method = self::getMethodAsPublic('getMappedInteger');

        $value = "MAY_SOMETHING";

        self::expectException(InvalidPeriodValueException::class);
        $result = $method->invokeArgs($this->object, [$value, $this->months]);

        self:self::assertEquals(5, $result);
    }

    public function test_getMappedInteger_integer() {
        $method = self::getMethodAsPublic('getMappedInteger');

        $value = 7;
        $result = $method->invokeArgs($this->object, [$value, $this->months]);

        self:self::assertEquals(7, $result);
    }

    public function test_getMappedInteger_string() {
        $method = self::getMethodAsPublic('getMappedInteger');

        $value = "15";
        $result = $method->invokeArgs($this->object, [$value, $this->months]);

        self:self::assertEquals(15, $result);
    }

    public function test_getMappedInteger_invalid_string() {
        $method = self::getMethodAsPublic('getMappedInteger');

        $value = "anything";

        self::expectException(InvalidPeriodValueException::class);
        $method->invokeArgs($this->object, [$value, $this->months]);
    }


}
