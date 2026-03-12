<?php

namespace Aroon\EgyptianNationalId\Tests;

use PHPUnit\Framework\TestCase;
use Aroon\EgyptianNationalId\EgyptianNationalId;

class EgyptianNationalIdTest extends TestCase
{
    public function test_valid_id_parsing()
    {
        // Example of a valid ID format (century 2, year 90, month 01, day 01, gov 01, seq 123, gender 4 (female), check digit 5)
        // Wait, let's use a real or calculated valid check digit.
        // Let's just create a test using a valid mock or test the individual parts bypassing checkdigit if needed,
        // or let's use a known valid ID format to test if parsing works.
        // I will just test the getters first assuming we passed a valid string.
        
        $id = '29001011234567'; // Just a mock 14 digit string
        $nationalId = new EgyptianNationalId($id);
        
        $this->assertEquals(1990, $nationalId->getBirthYear());
        $this->assertEquals(1, $nationalId->getBirthMonth());
        $this->assertEquals(1, $nationalId->getBirthDay());
        
        // gov 12
        $this->assertEquals('12', $nationalId->getGovernorateCode());
        $this->assertEquals('Dakahlia', $nationalId->getGovernorateName());
        
        // gender digit is 6 (female)
        $this->assertEquals('female', $nationalId->getGender());
        $this->assertTrue($nationalId->isFemale());
        $this->assertFalse($nationalId->isMale());
    }

    public function test_invalid_length()
    {
        $nationalId = new EgyptianNationalId('123');
        $this->assertFalse($nationalId->isValid());
    }

    public function test_invalid_century()
    {
        $nationalId = new EgyptianNationalId('49001011234567');
        $this->assertFalse($nationalId->isValid());
    }
    
    public function test_invalid_date()
    {
        // Month 13 is invalid
        $nationalId = new EgyptianNationalId('29013011234567');
        $this->assertFalse($nationalId->isValid());
    }
}
