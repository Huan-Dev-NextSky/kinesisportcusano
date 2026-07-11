Cleanto API request object :- 
1. add_customer
{"api_key":"1","email":"chopadelalit83@gmail.com","password":"12345678","first_name":"Lalit","last_name":"Chopade","phone":"8347545266","address":"mahadev nagar","zipcode":"394210","city":"surat","state":"Gujarat","action":"add_customer"}

2. cancel_appointment
{"api_key":"1","order_id":"1000","cancel_reason":"no reasone","action":"cancel_appointment"}

3. complete_appointment
{"api_key":"1","order_id":"1000","action":"complete_appointment"}

4. confirm_appointment
{"api_key":"1","order_id":"1000","action":"confirm_appointment"}

5. appointment_details
{"api_key":"1","order_id":"1000","action":"get_appointment_detail"}

6. reject_appointment
{"api_key":"1","order_id":"1000","reject_reason":"no reasone","action":"reject_appointment"}

7. reschedule_appointment
{"api_key":"1","order_id":"1000","notes":"new date","date":"2021-12-31","time":"13:00:00","action":"reschedule_appointment"}

8. 