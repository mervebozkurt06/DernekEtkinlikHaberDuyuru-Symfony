<?php

namespace App\Controller\Admin;

use App\Entity\Content;
use App\Form\ContentType;
use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/content")
 */
class ContentController extends AbstractController
{
    /**
     * @Route("/", name="admin_content_index", methods={"GET"})
     */
    public function index(ContentRepository $contentRepository): Response
    {
        $contents=$contentRepository->getAllContents();
        return $this->render('admin/content/index.html.twig', [
            'contents' => $contents,
        ]);
    }

    /**
     * @Route("/new", name="admin_content_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $content = new Content();
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager(); // submit edildiyse kutuphaneyi çağırır
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


            $entityManager->persist($content); //Create Row for Database
            $entityManager->flush(); //Save data to database

            return $this->redirectToRoute('admin_content_index');
        }

        return $this->render('admin/content/new.html.twig', [ //submit edilmemişse new çalışır
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_content_show", methods={"GET"})
     */
    public function show(Content $content): Response
    {
        return $this->render('admin/content/show.html.twig', [
            'content' => $content,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_content_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Content $content): Response
    {
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_content_index');
        }




        return $this->render('admin/content/edit.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }




    /**
     * @Route("/{id}", name="admin_content_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Content $content): Response
    {
        if ($this->isCsrfTokenValid('delete'.$content->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($content);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_content_index');
    }
    /*
    * @return string
    */

    private function generatedUniqueFileName()
    {
        return md5(uniqid());
    }
}
