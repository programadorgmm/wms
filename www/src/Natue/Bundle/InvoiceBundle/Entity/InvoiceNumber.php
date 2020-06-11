<?php

namespace Natue\Bundle\InvoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InvoiceNumber
 *
 * @ORM\Table(name="invoice_number", indexes={
 *     @ORM\Index(name="invoice_series_number", columns={"series", "number"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"series", "number"})
 * })
 * @ORM\Entity(repositoryClass="Natue\Bundle\InvoiceBundle\Repository\InvoiceNumberRepository")
 * @ORM\HasLifecycleCallbacks
 */
class InvoiceNumber
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="series", type="integer", length=3, options={"unsigned": true})
     */
    private $series;

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer", length=9, options={"unsigned": true})
     */
    private $number;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_recyclable", type="boolean", options={"default": 0})
     */
    private $isRecyclable = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set series
     *
     * @param int $series
     * @return InvoiceNumber
     */
    public function setSeries($series)
    {
        $this->series = $series;

        return $this;
    }

    /**
     * Get series
     *
     * @return int
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Set number
     *
     * @param integer $number
     * @return InvoiceNumber
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Get isRecyclable
     *
     * @return bool
     */
    public function isRecyclable()
    {
        return $this->isRecyclable;
    }

    /**
     * Set recyclability
     *
     * @param $isRecyclable
     * @return InvoiceNumber
     */
    public function setIsRecyclable($isRecyclable)
    {
        $this->isRecyclable = $isRecyclable;

        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return InvoiceNumber
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return InvoiceNumber
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Gets triggered only on insert
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->setCreatedAt(new \DateTime("now"));
    }

    /**
     * Gets triggered every time on update
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->setUpdatedAt(new \DateTime("now"));
    }
}
