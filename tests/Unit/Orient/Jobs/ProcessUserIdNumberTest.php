<?php

namespace Tests\Unit\Jobs;

use PHPUnit\Framework\TestCase;
use App\Jobs\ProcessUserIdNumber;

class ProcessUserIdNumberTest extends TestCase
{
    public function testIsValidSAIdNumberValid()
    { 
        $validId = '8112045070088';
        $this->assertTrue(ProcessUserIdNumber::isValidSAIdNumber($validId));
    }

    public function testIsValidSAIdNumberInvalid()
    { 
        $invalidId = '8992045070088'; 
        $this->assertFalse(ProcessUserIdNumber::isValidSAIdNumber($invalidId));
    }
}
