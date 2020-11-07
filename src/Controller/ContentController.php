<?php

namespace App\Controller;

use App\Entity\Content;
use App\Form\Content1Type;
use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/content")
 */
class ContentController extends AbstractController
{
    /**
     * @Route("/", name="user_content_index", methods={"GET"})
     */
    public function index(ContentRepository $contentRepository): Response
    {
        $user=$this->getUser();

        return $this->render('content/index.html.twig', [
            'contents' => $contentRepository->findBy(['userid'=>$user->getId()]),
        ]);
    }

    /**
     * @Route("/new", name="user_content_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $content = new Content();
        $form = $this->createForm(Content1Type::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            //*************file upload*>>>>>>
            $file= $form['image']->getData();

            // this condition is needed because the 'image' field is not required
            // so the PDF file must be processed only when a file is uploaded

            if ($file) {
                $fileName = $this->generatedUniqueFileName() . '.' . $file->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $file->move(
                        $this->getParameter('images_directory'), //Servis.yaml de tanımladığımız resim yolu
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $content->setImage($fileName);
            }

            //*************file upload*>>>>>>
            $user=$this->getUser();
            $content->setUserid($user->getId());
            $content->setStatus("New");

            $entityManager->persist($content);
            $entityManager->flush();

            return $this->redirectToRoute('user_content_index');
        }

        return $this->render('content/new.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_content_show", methods={"GET"})
     */
    public function show(Content $content): Response
    {
        return $this->render('content/show.html.twig', [
            'content' => $content,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_content_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Content $content): Response
    {
        $form = $this->createForm(Content1Type::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file= $form['image']->getData();
            if ($file) {
                $fileName = $this->generatedUniqueFileName() . '.' . $file->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $file->move(
                        $this->getParameter('images_directory'), //Servis.yaml de tanımladığımız resim yolu
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $content->setImage($fileName);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_content_index');
        }

        return $this->render('content/edit.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }
    /*
    * @return string
    */
    private function generatedUniqueFileName()
    {
        return md5(uniqid());
    }

    /**
     * @Route("/{id}", name="user_content_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Content $content): Response
    {
        if ($this->isCsrfTokenValid('delete'.$content->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($content);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_content_index');
    }
}
