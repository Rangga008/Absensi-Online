# TODO List for Attendance PDF Export Update

## Completed Tasks
- [x] Update export_pdf.blade.php to Indonesian language
- [x] Remove longitude and latitude columns from export_pdf.blade.php
- [x] Change "Checkout Status" to "Status Checkout" and "Checkout Time" to "Waktu Checkout" in export_pdf.blade.php
- [x] Make the layout neater by adjusting table structure
- [x] Update export_user_pdf.blade.php to Indonesian language
- [x] Remove Latitude and Longitude columns from export_user_pdf.blade.php
- [x] Change English labels to Indonesian in export_user_pdf.blade.php
- [x] Adjust colspan in empty table row for export_user_pdf.blade.php

## Pending Tasks
- [ ] Test the PDF export functionality to ensure it works correctly
- [ ] Verify that the generated PDF displays in Indonesian and excludes lat/long
- [ ] Confirm the layout is neat and readable

## Notes
- The controller methods (exportPdf and exportUserPdf) do not need changes as they pass data correctly to the updated views.
- All text in the PDF templates is now in Bahasa Indonesia.
- Longitude and latitude data are no longer displayed in the PDF exports.
- Table layouts have been optimized for better readability.
