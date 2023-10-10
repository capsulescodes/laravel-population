<?php

namespace CapsulesCodes\Population\Traits;

use Symfony\Component\Console\Output\OutputInterface;


trait WriteTrait
{
    protected $output;

    public function setOutput( OutputInterface $output )
    {
        $this->output = $output;

        return $this;
    }

    public function write( $component, ...$arguments )
    {
        if( $this->output && class_exists( $component ) )
        {
            ( new $component( $this->output ) )->render( ...$arguments );
        }
        else
        {
            foreach( $arguments as $argument )
            {
                if( is_callable( $argument ) )
                {
                    $argument();
                }
            }
        }
    }
}
