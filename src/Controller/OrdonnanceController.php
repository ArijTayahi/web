<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\LigneOrdonnance;
use App\Entity\Ordonnance;
use App\Repository\OrdonnanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ordonnance')]
final class OrdonnanceController extends AbstractController
{
    #[IsGranted('ROLE_PHYSICIAN')]
    #[Route('/create/{consultationId}', name: 'app_ordonnance_create', methods: ['GET', 'POST'])]
    public function create(
        int $consultationId,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $consultation = $entityManager->getRepository(Consultation::class)->find($consultationId);

        if (!$consultation) {
            throw $this->createNotFoundException('Consultation non trouvée.');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($consultation->getMedecin()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($consultation->getOrdonnance()) {
            return $this->redirectToRoute('app_ordonnance_show', ['id' => $consultation->getOrdonnance()->getId()]);
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('create_ordonnance_' . $consultationId, $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_ordonnance_create', ['consultationId' => $consultationId]);
            }

            $instructions = trim((string) $request->request->get('instructions'));
            $medicaments = $request->request->all('medicaments');

            if (empty($medicaments)) {
                $this->addFlash('error', 'Veuillez ajouter au moins un médicament.');
                return $this->redirectToRoute('app_ordonnance_create', ['consultationId' => $consultationId]);
            }

            $ordonnance = new Ordonnance();
            $ordonnance->setConsultation($consultation);
            $ordonnance->setInstructions($instructions !== '' ? $instructions : null);
            
            $dateValidite = new \DateTime();
            $dateValidite->modify('+30 days');
            $ordonnance->setDateValidite($dateValidite);

            foreach ($medicaments as $med) {
                $nom = trim((string) ($med['nom'] ?? ''));
                $dosage = trim((string) ($med['dosage'] ?? ''));
                $quantite = (int) ($med['quantite'] ?? 0);
                $duree = trim((string) ($med['duree'] ?? ''));
                $instructionsMed = trim((string) ($med['instructions'] ?? ''));

                if ($nom === '' || $dosage === '' || $quantite <= 0 || $duree === '') {
                    continue;
                }

                $ligne = new LigneOrdonnance();
                $ligne->setNomMedicament($nom);
                $ligne->setDosage($dosage);
                $ligne->setQuantite($quantite);
                $ligne->setDureeTraitement($duree);
                $ligne->setInstructions($instructionsMed !== '' ? $instructionsMed : null);
                $ligne->setOrdonnance($ordonnance);

                $entityManager->persist($ligne);
            }

            // Générer le QR Code
            $this->generateQrCode($ordonnance);

            $entityManager->persist($ordonnance);
            $entityManager->flush();

            $this->addFlash('success', 'Ordonnance créée avec succès.');

            return $this->redirectToRoute('app_ordonnance_show', ['id' => $ordonnance->getId()]);
        }

        return $this->render('ordonnance/create.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'app_ordonnance_show', methods: ['GET'])]
    public function show(Ordonnance $ordonnance): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $consultation = $ordonnance->getConsultation();

        if ($consultation->getPatient()->getId() !== $user->getId() 
            && $consultation->getMedecin()->getId() !== $user->getId()
            && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('ordonnance/show.html.twig', [
            'ordonnance' => $ordonnance,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/pdf', name: 'app_ordonnance_pdf', methods: ['GET'])]
    public function pdf(Ordonnance $ordonnance): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $consultation = $ordonnance->getConsultation();

        if ($consultation->getPatient()->getId() !== $user->getId() 
            && $consultation->getMedecin()->getId() !== $user->getId()
            && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = $this->renderView('ordonnance/pdf.html.twig', [
            'ordonnance' => $ordonnance,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="ordonnance_' . $ordonnance->getNumeroOrdonnance() . '.pdf"',
            ]
        );
    }

    private function generateQrCode(Ordonnance $ordonnance): void
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $qrDir = $projectDir . '/public/uploads/qrcode';

        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0775, true);
        }

        $result = Builder::create()
            ->data('ORD-' . $ordonnance->getNumeroOrdonnance())
            ->size(200)
            ->margin(10)
            ->build();

        $filename = $ordonnance->getNumeroOrdonnance() . '.png';
        $filepath = $qrDir . '/' . $filename;

        $result->saveToFile($filepath);

        $ordonnance->setQrCode('uploads/qrcode/' . $filename);
    }
}