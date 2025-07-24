<?php

namespace JulesGraus\Quatsch\Resources;

abstract class AbstractQuatschResource
{
    /**
     * @var resource
     */
    protected $handle;

    /**
     * @return resource
     */
    public function getHandle() {
        return $this->handle;
    }

    public function isSeekable(): bool
    {
        $meta = stream_get_meta_data($this->getHandle());
        return $meta['seekable'];
    }

    public function setBlocking(): static
    {
        stream_set_blocking($this->getHandle(), true);
        return $this;
    }
    public function setNonBlocking(): static
    {
        stream_set_blocking($this->getHandle(), false);
        return $this;
    }



    public function __destruct()
    {
        if(is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}