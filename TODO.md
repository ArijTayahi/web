# TODO: Fix Intelephense Errors in Entities

- [x] Update Doctor.php removeDocument: Change setDoctor($this) to setDoctor(null)
- [x] Update Doctor.php removeConsultation: Use a variable for $consultation->getDoctor() to avoid type mismatch
- [x] Update Patient.php removeConsultation: Use a variable for $consultation->getPatient() to resolve the error
