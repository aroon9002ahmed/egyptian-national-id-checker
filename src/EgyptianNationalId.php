<?php

namespace Aroon\EgyptianNationalId;

use DateTime;
use Exception;

class EgyptianNationalId
{
    private string $idStr;

    private const GOVERNORATES = [
        '01' => ['name' => 'Cairo', 'region' => 'Cairo'],
        '02' => ['name' => 'Alexandria', 'region' => 'Alexandria'],
        '03' => ['name' => 'Port Said', 'region' => 'Canal'],
        '04' => ['name' => 'Suez', 'region' => 'Canal'],
        '11' => ['name' => 'Damietta', 'region' => 'Delta'],
        '12' => ['name' => 'Dakahlia', 'region' => 'Delta'],
        '13' => ['name' => 'Ash Sharqia', 'region' => 'Delta'],
        '14' => ['name' => 'Kalyubia', 'region' => 'Delta'],
        '15' => ['name' => 'Kafr El Sheikh', 'region' => 'Delta'],
        '16' => ['name' => 'Gharbia', 'region' => 'Delta'],
        '17' => ['name' => 'Monufia', 'region' => 'Delta'],
        '18' => ['name' => 'Beheira', 'region' => 'Delta'],
        '19' => ['name' => 'Ismailia', 'region' => 'Canal'],
        '21' => ['name' => 'Giza', 'region' => 'Upper Egypt'],
        '22' => ['name' => 'Beni Suef', 'region' => 'Upper Egypt'],
        '23' => ['name' => 'Fayoum', 'region' => 'Upper Egypt'],
        '24' => ['name' => 'Minya', 'region' => 'Upper Egypt'],
        '25' => ['name' => 'Assiut', 'region' => 'Upper Egypt'],
        '26' => ['name' => 'Sohag', 'region' => 'Upper Egypt'],
        '27' => ['name' => 'Qena', 'region' => 'Upper Egypt'],
        '28' => ['name' => 'Aswan', 'region' => 'Upper Egypt'],
        '29' => ['name' => 'Luxor', 'region' => 'Upper Egypt'],
        '31' => ['name' => 'Red Sea', 'region' => 'Frontier'],
        '32' => ['name' => 'New Valley', 'region' => 'Frontier'],
        '33' => ['name' => 'Matrouh', 'region' => 'Frontier'],
        '34' => ['name' => 'North Sinai', 'region' => 'Frontier'],
        '35' => ['name' => 'South Sinai', 'region' => 'Frontier'],
        '88' => ['name' => 'Outside the Republic', 'region' => 'Foreign'],
    ];

    public function __construct(string|int $id)
    {
        $this->idStr = self::sanitize((string) $id);
    }

    public static function sanitize(string $id): string
    {
        // Convert Arabic/Hindi numerals to English numerals
        $arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $id = str_replace($arabicNumerals, $englishNumerals, $id);

        // Remove any non-digit characters (spaces, dashes, etc.)
        return preg_replace('/[^\d]/', '', $id);
    }

    public static function parse(string|int $id): self
    {
        return new self($id);
    }

    // --- Static Safe Helpers ---

    public static function isValidId(string|int $id): bool
    {
        try {
            return (new self($id))->isValid();
        } catch (Exception $e) {
            return false;
        }
    }

    public static function checkIsMale(string|int $id): bool
    {
        $instance = new self($id);
        return $instance->isValid() && $instance->isMale();
    }

    public static function checkIsFemale(string|int $id): bool
    {
        $instance = new self($id);
        return $instance->isValid() && $instance->isFemale();
    }

    public static function checkIsAdult(string|int $id): bool
    {
        $instance = new self($id);
        return $instance->isValid() && $instance->isAdult();
    }

    // --- Generator ---

    public static function generate(array $options = []): string
    {
        $year = $options['year'] ?? random_int(1950, (int) date('Y'));
        $month = $options['month'] ?? random_int(1, 12);
        
        $maxDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = $options['day'] ?? random_int(1, $maxDay);
        
        if (isset($options['governorate'])) {
            $gov = str_pad((string)$options['governorate'], 2, '0', STR_PAD_LEFT);
        } else {
            $govKeys = array_keys(self::GOVERNORATES);
            $gov = (string) $govKeys[array_rand($govKeys)];
        }
        
        $centuryDigit = ($year >= 2000) ? 3 : 2;
        $yearDigits = substr((string) $year, -2);
        $monthDigits = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $dayDigits = str_pad((string) $day, 2, '0', STR_PAD_LEFT);
        
        $isFemale = isset($options['gender']) 
            ? ($options['gender'] === 'female') 
            : (random_int(0, 1) === 0);
        
        $sequenceStart = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        
        if ($isFemale) {
            $genderDigit = random_int(0, 4) * 2;
        } else {
            $genderDigit = random_int(0, 4) * 2 + 1;
        }
        
        $idWithoutCheck = $centuryDigit . $yearDigits . $monthDigits . $dayDigits . $gov . $sequenceStart . $genderDigit;
        
        $multipliers = [2, 7, 6, 5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += ((int) $idWithoutCheck[$i]) * $multipliers[$i];
        }
        $remainder = $sum % 11;
        $checkDigit = abs(11 - $remainder) % 10;
        
        return $idWithoutCheck . $checkDigit;
    }

    public function isValid(): bool
    {
        if (!preg_match('/^\d{14}$/', $this->idStr)) {
            return false;
        }

        $centuryDigit = (int) $this->idStr[0];
        if (!in_array($centuryDigit, [2, 3], true)) {
            return false;
        }

        if (!$this->isValidDate()) {
            return false;
        }

        if ($this->getBirthDate() > new DateTime()) {
            return false;
        }

        $govCode = $this->getGovernorateCode();
        if (!isset(self::GOVERNORATES[$govCode])) {
            return false;
        }

        if (!$this->isValidCheckDigit()) {
            return false;
        }

        return true;
    }

    public function getBirthYear(): int
    {
        $centuryDigit = (int) $this->idStr[0];
        $year = (int) substr($this->idStr, 1, 2);
        
        $century = ($centuryDigit === 2) ? 1900 : 2000;

        return $century + $year;
    }

    public function getBirthMonth(): int
    {
        return (int) substr($this->idStr, 3, 2);
    }

    public function getBirthDay(): int
    {
        return (int) substr($this->idStr, 5, 2);
    }

    public function getBirthDate(): ?DateTime
    {
        if (!$this->isValidDate()) {
            return null;
        }

        try {
            return new DateTime(sprintf(
                '%04d-%02d-%02d',
                $this->getBirthYear(),
                $this->getBirthMonth(),
                $this->getBirthDay()
            ));
        } catch (Exception $e) {
            return null; // @codeCoverageIgnore
        }
    }

    public function getAge(): ?int
    {
        $birthDate = $this->getBirthDate();
        if (!$birthDate) {
            return null;
        }
        $now = new DateTime();
        return $now->diff($birthDate)->y;
    }

    public function getGovernorateCode(): string
    {
        return substr($this->idStr, 7, 2);
    }

    public function getGovernorateName(): ?string
    {
        $code = $this->getGovernorateCode();
        return self::GOVERNORATES[$code]['name'] ?? null;
    }

    public function getRegion(): string
    {
        $code = $this->getGovernorateCode();
        return self::GOVERNORATES[$code]['region'] ?? 'Foreign';
    }

    public function getGender(): string
    {
        $type = (int) $this->idStr[12];
        return ($type % 2 === 0) ? 'female' : 'male';
    }

    public function isMale(): bool
    {
        return $this->getGender() === 'male';
    }

    public function isFemale(): bool
    {
        return $this->getGender() === 'female';
    }

    public function isAdult(): bool
    {
        $age = $this->getAge();
        return $age !== null && $age >= 18;
    }

    public function isInsideEgypt(): bool
    {
        return $this->getGovernorateCode() !== '88';
    }

    public function toArray(): ?array
    {
        if (!$this->isValid()) {
            return null;
        }

        return [
            'nationalId' => $this->idStr,
            'birthYear' => $this->getBirthYear(),
            'birthMonth' => $this->getBirthMonth(),
            'birthDay' => $this->getBirthDay(),
            'age' => $this->getAge(),
            'gender' => $this->getGender(),
            'governorate' => $this->getGovernorateName(),
            'region' => $this->getRegion(),
            'insideEgypt' => $this->isInsideEgypt(),
            'isAdult' => $this->isAdult(),
        ];
    }

    private function isValidDate(): bool
    {
        $year = $this->getBirthYear();
        $month = $this->getBirthMonth();
        $day = $this->getBirthDay();

        return checkdate($month, $day, $year);
    }

    private function isValidCheckDigit(): bool
    {
        $checkDigit = (int) $this->idStr[13];
        $multipliers = [2, 7, 6, 5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += ((int) $this->idStr[$i]) * $multipliers[$i];
        }
        
        $remainder = $sum % 11;
        $expectedCheckDigit = abs(11 - $remainder) % 10;
        
        return $checkDigit === $expectedCheckDigit;
    }
}
