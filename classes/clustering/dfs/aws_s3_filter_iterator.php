<?php

class OpenPADFSFileHandlerDFSAmazonFilterIterator extends FilterIterator
{
    /**
     * Filters directories out
     */
    public function accept()
    {
        return true;
    }

    /**
     * Transforms the SplFileInfo in a simple relative path
     *
     * @return string The relative path to the current file
     */
    public function current()
    {
        $current = $this->getInnerIterator()->current();
        return $current['Key'];
    }
}
