<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Form\ContactType;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Try to send the email
            try {
                $email = (new Email())
                    ->from($data['email'])
                    ->to('chediouerghi88@gmail.com')
                    ->subject('Contact Us Form Submission')
                    ->text($data['message']);

                // Send the email
                $mailer->send($email);

                // Add a success flash message
                $this->addFlash('success', 'Your message has been sent!');
                
                // Redirect to the same contact page
                return $this->redirectToRoute('contact');
            } catch (\Exception $e) {
                // Add an error flash message if email sending fails
                $this->addFlash('error', 'There was an error sending your message. Please try again later.');
            }
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
