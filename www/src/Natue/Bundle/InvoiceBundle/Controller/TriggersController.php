<?php

namespace Natue\Bundle\InvoiceBundle\Controller;

use Natue\Bundle\InvoiceBundle\Entity\Invoice;
use Natue\Bundle\InvoiceBundle\Repository\InvoiceRepository;
use Natue\Bundle\InvoiceBundle\Services\InvoiceService;
use Natue\Bundle\InvoiceBundle\Taxman;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class TriggersController
 * @package Natue\Bundle\InvoiceBundle\Controller
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class TriggersController extends Controller
{
    /**
     * @Route("/triggers/update/{invoice}", name="invoice.triggers.update")
     * @Method({"GET"})
     * @param int $invoice
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateTrigger($invoice)
    {
        /** @var InvoiceRepository $invoiceRepository */
        $invoiceRepository = $this->get('natue.invoice.repository');

        /** @var Invoice $invoice */
        $invoice = $invoiceRepository->find($invoice);

        /** @var InvoiceService $invoiceService */
        $invoiceService = $this->get('natue.invoice.service');

        $invoiceService->processOutcome($invoice);

        return new JsonResponse(['success' => true]);
    }
}
