# TODO List for MedTime Homepage and Patient Dashboard Updates

## Homepage Modifications
- [ ] Modify templates/home/index.html.twig to display only "Connexion" and "Créer un compte" buttons in a centered layout, removing the full landing page content.

## Patient Dashboard Updates
- [ ] Add a top navbar to templates/dashboard/patient_dashboard.html.twig with buttons: Prendre un Rendez-vous, Produits médicaux, Actualités médicales ou Publications médecins.
- [ ] Enhance the sidebar to show patient profile and sub-buttons under "Appointments" (RDV): all consultations, all prescriptions, cabinet appointment, online appointment, all invoices, evaluate doctor. Use collapsible sections or JavaScript for interactivity.
- [ ] Implement availability checks for online appointments using Availability entity.
- [ ] Integrate online payment for online consultations using Paiement entity.
- [ ] Allow product purchases based on prescriptions, linking to Product entity.
- [ ] Add evaluation feature after consultations using Satisfaction entity.

## Controller and Route Updates
- [ ] Verify and create missing routes in controllers (e.g., RendezVousController, PaiementController) for new functionalities.
- [ ] Ensure HomeController redirects logged-in users correctly.

## Testing and Followup
- [ ] Test availability checks, payments, evaluations, and product purchases.
- [ ] Ensure responsive design for new navbar and sidebar.
- [ ] Verify no changes to existing code; only additions as per requirements.
