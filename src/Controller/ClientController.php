<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Service\ClientService;
use Gedmo\Loggable\Entity\LogEntry;
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
     * @Route("/", name="client_index", methods={"GET"})
     * @return Response
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
     * @Route("/new", name="client_new", methods={"GET","POST"})
     * @return Response
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
     * @Route("/{id}", name="client_show", methods={"GET"})
     * @return Response
     */
    public function show(Client $client): Response
    {
        $logEntry = $this->getDoctrine()->getManager()->getRepository('Gedmo\Loggable\Entity\LogEntry');
        return $this->render('client/show.html.twig', [
            'client' => $client,
            'history' => $logEntry->getLogEntries($client),
        ]);
    }

    /**
     * @param Request $request
     * @param Client $client
     * @Route("/{id}/edit", name="client_edit", methods={"GET","POST"})
     * @return Response
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
     * @Route("/{id}", name="client_delete", methods={"DELETE"})
     * @return Response
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
