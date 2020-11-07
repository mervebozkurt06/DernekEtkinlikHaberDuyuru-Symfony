<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="admin_image_index", methods={"GET"})
     */
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('admin/image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="admin_image_new", methods={"GET","POST"})
     */
    public function new(Request $request, $id, ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { //ImageType'a git resim uygun mu?
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

                $image->setImage($fileName);
            }

            //*************file upload*>>>>>>

            $entityManager->persist($image);
            $entityManager->flush();

            return $this->redirectToRoute('admin_image_new',['id'=>$id]); //resmi upload ettikten sonra aynı yere dönebilmek için id
        }

        $images=$imageRepository->findBy(['content'=>$id]);
        return $this->render('admin/image/new.html.twig', [
            'image' => $image,
            'id'=>$id,
            'images'=>$images,

            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_image_show", methods={"GET"})
     */
    public function show(Image $image): Response
    {
        return $this->render('admin/image/show.html.twig', [
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_image_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Image $image): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_image_index');
        }

        return $this->render('admin/image/edit.html.twig', [
            'image' => $image,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{cid}", name="admin_image_delete", methods={"DELETE"})
     */
    public function delete(Request $request, $cid ,Image $image): Response
    {
        //echo "content id:".$cid;
        //die();
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image); //silme
            $entityManager->flush(); // veritabanı güncelleme
        }

        return $this->redirectToRoute('admin_image_new',['id'=>$cid]); //silip aynı sayfaya dönmek için
    }

    private function generatedUniqueFileName()
    {
        return md5(uniqid());
    }
}
