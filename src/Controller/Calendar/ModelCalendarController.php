<?php

namespace App\Controller\Calendar;

use Symfony\Component\Uid\Uuid;
use App\Service\Utils\UploadHandler;
use App\Entity\Calendar\ModelCalendar;
use App\Form\Calendar\ModelCalendarType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\Calendar\ModelCalendarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class ModelCalendarController extends AbstractController
{
    #[Route('/admin/calendriers/index', name: 'model_calendar_index', methods: ['GET'])]
    public function index(ModelCalendarRepository $modelCalendarRepository): Response
    {
        return $this->render('calendar/model_calendar/index.html.twig', [
            'models' => $modelCalendarRepository->findAll(),
        ]);
    }

    #[Route('/admin/calendriers/new', name: 'model_calendar_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ModelCalendarRepository $modelCalendarRepository, UploadHandler $uploadHandler): Response
    {
        $modelCalendar = new ModelCalendar();
        $form = $this->createForm(ModelCalendarType::class, $modelCalendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            if ($file !== null) {
                try {
                    $newFilename = $uploadHandler->uploadFile($file, $request->get('cropped-file'));
                    $modelCalendar->setPath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        'Une erreur s\'est produite lors de l\'upload'
                    );

                    return $this->render('calendar/model_calendar/new.html.twig', [
                        'model' => $modelCalendar,
                        'form' => $form,
                    ]);
                }
            }

            $modelCalendar->setUser($this->getUser());
            $modelCalendar->setState('published');
            $uuid = Uuid::v4();
            $modelCalendar->setUuid($uuid);

            $modelCalendarRepository->save($modelCalendar, true);

            $this->addFlash(
                'successs',
                'Le modèle a bien été créé'
            );

            return $this->redirectToRoute('model_calendar_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('calendar/model_calendar/new.html.twig', [
            'model' => $modelCalendar,
            'form' => $form,
        ]);
    }

    #[Route('/admin/calendriers/{id}', name: 'model_calendar_show', methods: ['GET'])]
    public function show(ModelCalendar $modelCalendar): Response
    {
        return $this->render('calendar/model_calendar/show.html.twig', [
            'model' => $modelCalendar,
        ]);
    }

    #[Route('/admin/calendriers/{id}/edit', name: 'model_calendar_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ModelCalendar $modelCalendar, ModelCalendarRepository $modelCalendarRepository, UploadHandler $uploadHandler): Response
    {
        $form = $this->createForm(ModelCalendarType::class, $modelCalendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            if ($file !== null) {
                try {
                    $newFilename = $uploadHandler->uploadFile($file, $request->get('cropped-file'));
                    $modelCalendar->setPath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash(
                        'error',
                        'Une erreur s\'est produite lors de l\'upload'
                    );

                    return $this->render('calendar/model_calendar/edit.html.twig', [
                        'model' => $modelCalendar,
                        'form' => $form,
                    ]);
                }
            }

            $modelCalendarRepository->save($modelCalendar, true);

            $this->addFlash(
                'successs',
                'Le modèle a bien été édité'
            );

            return $this->redirectToRoute('model_calendar_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('calendar/model_calendar/edit.html.twig', [
            'model' => $modelCalendar,
            'form' => $form,
        ]);
    }

    #[Route('/admin/calendriers/{id}', name: 'model_calendar_delete', methods: ['POST'])]
    public function delete(Request $request, ModelCalendar $modelCalendar, ModelCalendarRepository $modelCalendarRepository): Response
    {

        if ($this->isCsrfTokenValid('delete_'.$modelCalendar->getId(), $request->request->get('_token'))) {
            $modelCalendarRepository->remove($modelCalendar, true);
        }

        return $this->redirectToRoute('model_calendar_index', [], Response::HTTP_SEE_OTHER);
    }
}