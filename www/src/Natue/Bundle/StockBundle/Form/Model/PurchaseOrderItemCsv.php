<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Check if the csv file is valid
 */
class PurchaseOrderItemCsv
{
    /**
     * @Assert\Valid()
     * @Assert\File(
     *     maxSize          = "10M",
     *     maxSizeMessage   = "The file is too large. Allowed maximum size is 10 MegaBytes.",
     *     mimeTypes        = {"text/csv", "text/plain"},
     *     mimeTypesMessage = "The file uploaded is not a CSV file. Please upload a csv file.",
     *     notFoundMessage  = "The CSV file could not be found."
     * )
     */
    protected $submitFile;

    /**
     * @return mixed
     */
    public function getSubmitFile()
    {
        return $this->submitFile;
    }

    /**
     * @param $submitFile
     *
     * @return void
     */
    public function setSubmitFile($submitFile)
    {
        $this->submitFile = $submitFile;
    }
}
