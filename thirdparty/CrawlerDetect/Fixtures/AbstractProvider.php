<?php
/**
 * This file is part of Crawler Detect - the web crawler detection library.
 *
 * @author (c) Mark Beech <m@rkbee.ch>
 * @copyright (c) Mark Beech <m@rkbee.ch>
 * @license MIT License
 */
abstract class AbstractProvider
{
    /**
     * The data set.
     *
     * @var array
     */
    protected $data;

    /**
     * Return the data set.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }
}
