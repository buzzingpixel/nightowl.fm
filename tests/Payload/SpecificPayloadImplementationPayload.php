<?php

declare(strict_types=1);

namespace Tests\Payload;

use App\Payload\SpecificPayload;

class SpecificPayloadImplementationPayload extends SpecificPayload
{
    /** @var mixed */
    private $bar;

    /**
     * @param mixed $val
     */
    protected function setBar($val): void
    {
        $this->bar = $val;
    }

    /**
     * @return mixed
     */
    public function getBar()
    {
        return $this->bar;
    }
}
