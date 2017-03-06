<?php

namespace AppBundle\Service;

class FlushService
{
    public function removeDir($dir)
    {
        $objects = scandir($dir); 

        foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
                if (is_dir($dir."/".$object)) {
                    $this->removeDir($dir."/".$object);
                } else {
                    unlink($dir."/".$object);
                }
            }
        }

        rmdir($dir);
    }
}
