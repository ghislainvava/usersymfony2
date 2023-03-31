<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\services\EntityServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    #[Route("/tasks", name:"task_list", methods: ['GET'])]
    public function listAction(EntiTyManagerInterface $em)
    {
        return $this->render('task/list.html.twig', ['tasks' => $em->getRepository(Task::class)->findAll()]);
    }

    #[Route("/tasks/create", name:"task_create")]
    public function createAction(Request $request,  EntityServices $entityservices)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        $client = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {

            $entityservices->eManager($task, $client);
            $this->addFlash('success', 'La tâche a été bien été ajoutée.');
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route("/tasks/{id}/edit", name:"task_edit")]
    public function editAction(Task $task, Request $request, EntityServices $entityservices)
    {
        if ($this->getUser()->getId() != $task->getUser()->getId() AND $this->isGranted('ROLE_ADMIN') == false) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette tache');

            return $this->redirectToRoute('task_list');
        }
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        $client = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreatedAt(new \DateTimeImmutable());
            $entityservices->eManager($task, $client);
            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route("/tasks/{id}/toggle", name:"task_toggle")]
    public function toggleTaskAction(Task $task, EntityManagerInterface $em)
    {

        if ($this->getUser()->getId() == $task->getUser()->getId() || $this->isGranted('ROLE_ADMIN')) {
           
                $task->toggle(!$task->isDone());
                $em->flush();
                $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle())); 
                
                return $this->redirectToRoute('task_list');   

            } 
            $this->addFlash('error', "Vous ne pouvez pas marquer cette tâche comme faîtes puisque que vous en n'êtes pas l'auteur");
            return $this->redirectToRoute('task_list');
          
    }

    #[Route("/tasks/{id}/delete", name:"task_delete")]
    public function deleteTaskAction(Task $task,  EntityManagerInterface $em)
    {
        
        if ($this->getUser()->getId() == $task->getUser()->getId() || $this->isGranted('ROLE_ADMIN')) {
            $em->remove($task);
            $em->flush();
            $this->addFlash('success', 'La tâche a bien été supprimée.');
            return $this->redirectToRoute('task_list');   
        } 
        
        $this->addFlash('error', "Vous n'êtes pas autorisé à supprimer cette tâche car vous n'êtes pas l'auteur");
        return $this->redirectToRoute('task_list');
    }
}
