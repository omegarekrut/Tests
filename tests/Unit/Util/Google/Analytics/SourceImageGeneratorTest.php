<?php

namespace Tests\Unit\Util\Google\Analytics;

use App\Util\Google\Analytics\RandomGenerator;
use App\Util\Google\Analytics\SourceImageGenerator;
use Tests\Unit\TestCase;

class SourceImageGeneratorTest extends TestCase
{
    public function testGenerateSourceImage(): void
    {
        $email = 'admin@gmail.com';
        $number = 1;
        $domain = 'fishingsib.test';
        $accountString = 'UA-34239366-1';

        $expectedUrl = $this->getExpectedUrl($email, $number, $domain, $accountString);

        $sourceImageGenerator = new SourceImageGenerator($accountString, $domain, $this->getRandomGeneratorMock());
        $actualUrl = $sourceImageGenerator->getSourceImage($email, $number);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    private function getRandomGeneratorMock(): RandomGenerator
    {
        $stub = $this->createMock(RandomGenerator::class);
        $stub
            ->method('generateInteger')
            ->willReturn(123);
        $stub
            ->method('generateTimestamp')
            ->willReturn(456);

        return $stub;
    }

    private function getExpectedUrl(string $userEmail, int $number, string $domain, string $accountString): string
    {
        $generatorMock = $this->getRandomGeneratorMock();

        $randomInt = $generatorMock->generateInteger(1, 1);
        $randomTimestamp = $generatorMock->generateTimestamp();

        return '//www.google-analytics.com/__utm.gif'
            .'?utmwv=1'
            .'&utmn='.$randomInt
            .'&utmsr=-'
            .'&utmsc=-'
            .'&utmul=-'
            .'&utmje=0'
            .'&utmfl=-'
            .'&utmdt=-'
            .'&utmhn='.$domain
            .'&utmr=-'
            .'&utmp=%2FEmailViews%2Fsubscribe_number_'.$number.'%2F'
            .'&utmac='.$accountString
            .'&utmcc=__utma%3D'.$randomInt.'.'.$randomInt.'.'.$randomTimestamp.'.'.$randomTimestamp.'.'.$randomTimestamp.'.2%3B%2B__utmb%3D'.$randomInt.'%3B%2B__utmc%3D'.$randomInt.'%3B%2B__utmz%3D'.$randomInt.'.'.$randomTimestamp.'.2.2.utmccn%3Dviewemail%7Cutmcsr%3D'.urlencode($userEmail).'%7Cutmcmd%3Dviewemail%3B%2B__utmv%3D'.$randomInt.'.-%3B';
    }
}
