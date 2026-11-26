<?php

namespace Tests\Feature;

use Tests\TestCase;

class TimezoneTest extends TestCase
{
    public function test_la_aplicacion_usa_la_hora_local_de_xalapa(): void
    {
        $this->assertSame('America/Mexico_City', config('app.timezone'));
        $this->assertSame('America/Mexico_City', now()->getTimezone()->getName());
        $this->assertSame(-6, (int) (now()->getOffset() / 3600));
    }
}
