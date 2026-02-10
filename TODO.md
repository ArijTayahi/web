# TODO: Fix Intelephense Errors in Entities

- [x] Update Doctor.php removeDocument: Change setDoctor($this) to setDoctor(null)
- [x] Update Doctor.php removeConsultation: Use a variable for $consultation->getDoctor() to avoid type mismatch
- [x] Update Patient.php removeConsultation: Use a variable for $consultation->getPatient() to resolve the error

# TODO: Fix Twig Null Pointer Errors

- [x] Fix consultation/index.html.twig: Add null check for doctor.user.username
- [x] Fix consultation/show.html.twig: Add null check for doctor.user.username
- [x] Fix ordonnance/index.html.twig: Add null check for doctor.user.username
- [x] Fix patient/consultations.html.twig: Add null check for doctor.user.username
- [x] Fix admin/doctor_verifications.html.twig: Add null check for doctor.user.username

# TODO: Test Consultation Module

- [ ] Test consultation creation (patient role)
- [ ] Test consultation listing (patient role)
- [ ] Test consultation details view (patient role)
- [ ] Test consultation editing (patient role)
- [ ] Test consultation cancellation (patient role)
- [ ] Test doctor consultation management (doctor role)
- [ ] Test prescription access (both roles)

# TODO: Add Form Validation

- [x] Add NotBlank constraint to doctor field
- [x] Add NotBlank constraint to consultationDate field
- [x] Add GreaterThanOrEqual constraint to consultationDate (not before now)
- [x] Add NotBlank constraint to type field

# TODO: Add Online Consultation Redirection

- [x] Add JavaScript to redirect to /consultation when "Online" is selected
