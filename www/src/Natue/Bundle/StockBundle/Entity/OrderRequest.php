<?php
namespace Natue\Bundle\StockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Natue\Bundle\UserBundle\Common\TrackableInterface;
use Natue\Bundle\ZedBundle\Entity\ZedSupplier;
use Natue\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity(repositoryClass="Natue\Bundle\StockBundle\Repository\OrderRequestRepository")
 *
 * @ORM\Table(
 *  name="order_request",
 *  indexes={@ORM\Index(name="order_request_fk_zed_supplier", columns={"zed_supplier"})}
 * )
 */
class OrderRequest implements TrackableInterface
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var ZedSupplier
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\ZedBundle\Entity\ZedSupplier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zed_supplier", referencedColumnName="id")
     * })
     */
    protected $zedSupplier;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    protected $description;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Natue\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id")
     * })
     */
    protected $user;

    /**
     * @var OrderRequestItem[]
     * @ORM\OneToMany(
     *    targetEntity="Natue\Bundle\StockBundle\Entity\OrderRequestItem",
     *    mappedBy="orderRequest",
     *    cascade={"persist"}
     * )
     */
    protected $items;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

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
     * Set description
     *
     * @param string $description
     * @return OrderRequest
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set zedSupplier
     *
     * @param ZedSupplier $zedSupplier
     * @return OrderRequest
     */
    public function setZedSupplier(ZedSupplier $zedSupplier = null)
    {
        $this->zedSupplier = $zedSupplier;

        return $this;
    }

    /**
     * Get zedSupplier
     *
     * @return ZedSupplier
     */
    public function getZedSupplier()
    {
        return $this->zedSupplier;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     * @return OrderRequest
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Natue\Bundle\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add items
     *
     * @param \Natue\Bundle\StockBundle\Entity\OrderRequestItem $items
     * @return OrderRequest
     */
    public function addItem(OrderRequestItem $items)
    {
        $this->items[] = $items;

        return $this;
    }

    /**
     * Remove items
     *
     * @param OrderRequestItem $items
     */
    public function removeItem(OrderRequestItem $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItems()
    {
        return $this->items;
    }
}
