<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class OrderRequest
{
    /**
     * @var integer
     *
     * @Assert\NotBlank(
     *      message    = "Supplier should not be blank"
     * )
     */
    protected $supplier;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Description should not be blank"
     * )
     */
    protected $description;

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
     * @return int
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param int $supplier
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getSubmitFile()
    {
        return $this->submitFile;
    }

    /**
     * @param mixed $submitFile
     */
    public function setSubmitFile($submitFile)
    {
        $this->submitFile = $submitFile;
    }
}