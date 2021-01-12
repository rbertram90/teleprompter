# Teleprompter Candidate test
Teleprompter Candidate test

## Setup
- Fork Repo
- Set up a new Drupal 9 instance.
  - [DDEV](https://ddev.readthedocs.io/en/latest/users/cli-usage/#drupal-8-quickstart) is quite easy to set up, otherwise docker.
- Clone teleprompter into `modules/custom`
- Enable Teleprompter Module
  - Ensure dependancies are met: restui, views
## Essential
- Update Teleprompter Module
 - Enable REST on endpoint "teleprompter_rest_resource" and use Basic Auth
   - Accept a POST request from Authenticated Users
 - Ensure only alpha characters(Max 255) are present in the "question" field and that the only field present is "question"; otherwise fail the request with 400 and return appropriate message
    - Format example can be found in /tests/question.json
 - Create a question entity
   - Set the Status to "show"
   - Return a 201 with appropriate message
- Update Teleprompter View to include the users First name and Surname
 - Add the changes to config/install

## Bonus
- Write some appropriate unit tests
- Update View to automatically refresh every 30 seconds(AJAX)
- Sanitise the question by making the first character capitalised and the last character is "?" if not present. 
  - Create a service to perform sanitisation