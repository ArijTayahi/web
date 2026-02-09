<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\Facture;
use App\Entity\Paiement;
use App\Entity\TransactionPaiement;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/paiement')]
final class PaiementController extends AbstractController
{
    #[IsGranted('ROLE_PATIENT')]
    #[Route('/consultation/{id}', name: 'app_paiement_consultation', methods: ['GET', 'POST'])]
    public function payer(
        Consultation $consultation,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getPatient()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($consultation->getPaiement()) {
            return $this->redirectToRoute('app_paiement_show', ['id' => $consultation->getPaiement()->getId()]);
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('paiement_consultation_' . $consultation->getId(), $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_paiement_consultation', ['id' => $consultation->getId()]);
            }

            $methode = (string) $request->request->get('methode');

            if (!in_array($methode, ['EDINAR', 'MOBILE_MONEY', 'SOBFLOUS'], true)) {
                $this->addFlash('error', 'Méthode de paiement invalide.');
                return $this->redirectToRoute('app_paiement_consultation', ['id' => $consultation->getId()]);
            }

            $paiement = new Paiement();
            $paiement->setConsultation($consultation);
            $paiement->setPatient($user);
            $paiement->setMontant(50.00); // Prix fixe pour l'exemple
            $paiement->setMethode($methode);
            $paiement->setStatus('PAYE'); // Simulation : paiement réussi directement

            $transaction = new TransactionPaiement();
            $transaction->setPaiement($paiement);
            $transaction->setStatus('SUCCESS');
            $transaction->setMessage('Paiement simulé avec succès');

            $entityManager->persist($paiement);
            $entityManager->persist($transaction);
            $entityManager->flush();

            // Générer la facture
            $this->genererFacture($paiement, $entityManager);

            $this->addFlash('success', 'Paiement effectué avec succès.');

            return $this->redirectToRoute('app_paiement_show', ['id' => $paiement->getId()]);
        }

        return $this->render('paiement/payer.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'app_paiement_show', methods: ['GET'])]
    public function show(Paiement $paiement): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($paiement->getPatient()->getId() !== $user->getId() 
            && $paiement->getConsultation()->getMedecin()->getId() !== $user->getId()
            && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('paiement/show.html.twig', [
            'paiement' => $paiement,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/facture/{id}/pdf', name: 'app_facture_pdf', methods: ['GET'])]
    public function facturePdf(Facture $facture): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($facture->getPaiement()->getPatient()->getId() !== $user->getId() 
            && $facture->getPaiement()->getConsultation()->getMedecin()->getId() !== $user->getId()
            && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        $html = $this->renderView('paiement/facture_pdf.html.twig', [
            'facture' => $facture,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="facture_' . $facture->getNumeroFacture() . '.pdf"',
            ]
        );
    }

    private function genererFacture(Paiement $paiement, EntityManagerInterface $entityManager): void
    {
        $facture = new Facture();
        $facture->setPaiement($paiement);
        $facture->setMontant($paiement->getMontant());

        if ($paiement->getConsultation()->getOrdonnance()) {
            $facture->setOrdonnance($paiement->getConsultation()->getOrdonnance());
        }

        $entityManager->persist($facture);
        $entityManager->flush();
    }
}