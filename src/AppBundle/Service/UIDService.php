<?php

namespace AppBundle\Service;

use Ramsey\Uuid\Uuid;

class UIDService
{
    public function generate()
    {
        return Uuid::uuid4()->toString();
    }
}
