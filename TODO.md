# Fix Delete Selected User Feature and Display

## Tasks
- [ ] Update select-all checkbox in table header to use proper Bootstrap form-check styling
- [ ] Update individual user checkboxes in table rows to use proper Bootstrap form-check styling
- [ ] Test the bulk delete functionality to ensure it works correctly
- [ ] Verify the display looks proper and checkboxes are aligned

## Notes
- The bulk delete feature uses soft delete (moves to trash)
- JavaScript handles enabling/disabling the delete button based on selections
- Checkboxes need to be wrapped in `<div class="form-check">` for proper Bootstrap styling
