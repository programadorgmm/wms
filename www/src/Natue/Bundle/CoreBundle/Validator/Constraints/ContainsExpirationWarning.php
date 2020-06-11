<?php

namespace Natue\Bundle\CoreBundle\Validator\Constraints;

class ContainsExpirationWarning
{

    /**
     * @int
     */
    const WARNING_DAYS_INTERVAL = 30;

    /**
     * @param \DateTime $value
     *
     * @return bool
     */
    public static function check(\DateTime $value)
    {
        $intervalSpec = sprintf('+%d days', self::WARNING_DAYS_INTERVAL);
        $intervalDate = \DateInterval::createFromDateString($intervalSpec);

        $safeDate = (new \DateTime('now'))->add($intervalDate);

        return ($value <= $safeDate);
    }

    /**
     * @return string
     */
    public static function getMessage()
    {
        return sprintf('PurchaseOrderItem DateExpiration is shorter than %d days', self::WARNING_DAYS_INTERVAL);
    }
}
