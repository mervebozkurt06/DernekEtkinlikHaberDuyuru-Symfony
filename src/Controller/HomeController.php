<?php

namespace App\Controller;

use App\Controller\Admin\CommentController;
use App\Entity\Admin\Messages;
use App\Entity\Content;
use App\Entity\Setting;
use App\Form\Admin\MessagesType;
use App\Repository\Admin\CommentRepository;
use App\Repository\ContentRepository;
use App\Repository\SettingRepository;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository $settingRepository, ContentRepository $contentRepository)
    {
        $setting=$settingRepository->findAll();
        $slider=$contentRepository->findBy([],['title'=>'ASC']);
        $contents=$contentRepository->findBy([],['title'=>'DESC'],null);
        $habercontents=$contentRepository->findBy(['type'=>'Haberler'],['type'=>'DESC'],10);
        $duyurucontents=$contentRepository->findBy(['type'=>'Duyurular'],['type'=>'DESC'],10);
        $etkinlikcontents=$contentRepository->findBy(['type'=>'Etkinlik'],['type'=>'DESC'],10);
        //array findBy(array $criteria, array $orderBy=null, int|null $limit=null, int|null $offset=null)
        //dump($slider);
        //die();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=>$setting,
            'slider'=>$slider,
            'contents'=>$contents,
            'habercontents'=>$habercontents,
            'duyurucontents'=>$duyurucontents,
            'etkinlikcontents'=>$etkinlikcontents,
        ]);
    }

    /**
     * @Route("/content/{id}", name="content_show", methods={"GET"})
     */
    public function show(Content $content,$id,  ImageRepository $imageRepository,CommentRepository $commentRepository): Response
    {
        $images=$imageRepository->findBy(['content'=>$id]);
        $comments=$commentRepository->findBy(['contentid'=>$id,'status'=>'True']);

        return $this->render('home/contentshow.html.twig', [
            'content' => $content,
            'images' => $images,
            'comments' => $comments,
        ]);
    }

    /**
     * @Route("/about", name="home_about")
     */
    public function about(SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'setting'=>$setting,
        ]);
    }

    /**
     * @Route("/contact", name="home_contact", methods={"GET","POST"})
     */
    public function contact(SettingRepository $settingRepository, Request $request): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        //dump($request);
        //die();
        $submittedToken = $request->request->get('token');
        $setting=$settingRepository->findAll();

        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('form-message',$submittedToken)){

                $entityManager = $this->getDoctrine()->getManager();
                $message->setStatus('New');
                $message->setIp($_SERVER['REMOTE_ADDR']);
                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success','Your message has been sent succesfully');

                //***********send email**************>>>>>>>
                $email = (new Email())
                    ->from($setting[0]->getSmtpemail())
                    ->to($form['email']->getData())
                    //->cc('cc@example.com')
                    //->bcc('bcc@example.com')
                    //->replyTo('fabien@example.com')
                    //->priority(Email::PRIORITY_HIGH)
                    ->subject('All Content Your Request')
                    //->text('Sending emails is fun again!')
                    ->html("Dear ".$form['name']->getData()."<br>
                            <p>We will evaluate your requests and contact you as soon as possible</p>
                            Thank you <br>
                            ===========================================
                            <br>".$setting[0]->getCompany()." <br>
                            Address : ".$setting[0]->getAddress()."<br>
                            Phone   : ".$setting[0]->getPhone()."<br>"
                    );
                $transport = new GmailSmtpTransport($setting[0]->getSmtpemail(), $setting[0]->getSmtppassword());
                //$transport=new GmailTransport($setting[0]->getSmtpemail(),$setting[0]->getSmtppassword());
                $mailer= new Mailer($transport);
                $mailer->send($email);

                //<<<<<<<<<<<<<<**********SEND EMAIL***********************

                return $this->redirectToRoute('home_contact');
            }

        }


        return $this->render('home/contact.html.twig', [
            'setting'=>$setting,
            'form' => $form->createView(),
        ]);
    }



}
