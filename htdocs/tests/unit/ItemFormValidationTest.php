<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ItemFormValidationTest extends CIUnitTestCase
{
    public function testAcceptsTheDateTimeLocalFormatUsedByTheItemForm(): void
    {
        $validation = service('validation');

        $this->assertTrue($validation->check('2026-10-02T16:30', 'valid_date[Y-m-d\\TH:i]'));
        $this->assertFalse($validation->check('not-a-date', 'valid_date[Y-m-d\\TH:i]'));
    }

    public function testEpisodeAndSeasonValuesMustBeNaturalNumbers(): void
    {
        $validation = service('validation');

        $this->assertTrue($validation->check('0', 'is_natural'));
        $this->assertTrue($validation->check('12', 'is_natural'));
        $this->assertFalse($validation->check('-1', 'is_natural'));
        $this->assertFalse($validation->check('1.5', 'is_natural'));
    }
}
