<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Service\ClientService;
use App\Service\LogEntryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/client")
 */
class ClientController extends AbstractController
{
    /**
     * @param ClientService $service
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="client_index", methods={"GET"})
     */
    public function index(ClientService $service, Request $request): Response
    {
        $pagination = $service->paginate($request);

        return $this->render('client/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/new", name="client_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            foreach ($client->getAddresses() as $address) {
                $address->setClient($client);

                $entityManager->persist($address);
            }

            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Client $client
     * @param LogEntryService $logEntryService
     * @return Response
     *
     * @Route("/{id}", name="client_show", methods={"GET"})
     */
    public function show(LogEntryService $logEntryService, Client $client): Response
    {
        $logEntries = $logEntryService->getEntityEntries($this->getDoctrine()->getManager(), $client);

        return $this->render('client/show.html.twig', [
            'client' => $client,
            'logEntries' => $logEntries,
        ]);
    }

    /**
     * @param Request $request
     * @param Client $client
     * @return Response
     *
     * @Route("/{id}/edit", name="client_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Client $client): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            foreach ($client->getAddresses() as $address) {
                $address->setClient($client);

                $entityManager->persist($address);
            }

            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('client_index', [
                'id' => $client->getId(),
            ]);
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Client $client
     * @return Response
     *
     * @Route("/{id}", name="client_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Client $client): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('client_index');
    }
}
