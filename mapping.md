+-----------------------------------------------------------------------------------------------+-----------------------------------------------+
| Origen 																						| Destino										|
+-----------------------------------------------------------------------------------------------+-----------------------------------------------+
| users.id 																						| users.id 										|
| role.name 																					| users.role 									|
| bf_users_options.first_name||users.data.first_name 											| users.name 									|
| bf_users_options.last_name||users.data.last_name 												| users.last_name  								|
| bf_users_options.address 																		| users.address 								|
| bf_users_options.zip||users.data.zip 															| users.zip_code 								|
| bf_users_options.company_name||bf_users_options.last_job_employer||users.data.uf_company_name | users.work 									|
| bf_users_options.user_title||bf_users_options.last_job_title 									| users.title 									|
| users.mail 																					| users.email 									|
| bf_users_options.about 																		| users.biography 								|
| bf_users_options.salesForceId 																| users.salesforce_id 							|
| users.picture 																				| users.profile_picture 						|
| users.pass 																					| users.password 								|
| bf_users_options.company_name||bf_users_options.last_job_employer||users.data.uf_company_name | companies.name (1)							|
| users.uid																						| companies.user_id								|
| bf_files.filepath																				| companies.logo								|
| bf_files.fid																					| user_resumes.id								|
| bf_files.uid																					| user_resumes.user_id							|
| bf_files.filename																				| user_resumes.name_file						|
| bf_files.fileurl																				| user_resumes.path_file						|
| bf_files.fid																					| user_cover_letter.id							|
| bf_files.uid																					| user_cover_letter.user_id						|
| bf_files.filename																				| user_cover_letter.name_file					|
| bf_files.fileurl																				| user_cover_letter.path_file					|
| term_data.value 																				| manufacturing_types.name (2)					|
| term_data.value 																				| beverage_types.name (3)						|
| term_data.value 																				| areas.name 									|
| content_type_job.nid																			| post_jobs.id									|		| node.uid																						| post_jobs.user_id								|		| companies.id(1)																				| post_jobs.company_id							|		| manufacturing_types(2)																		| post_jobs.manufacturing_type_id				|		| content_type_job.field_type_value 															| job_employment_types.name 					|
| beverage_types.id(3)																			| post_jobs.beverage_type_id					|		
| content_type_job.job_title||content_type_job.title 											| post_jobs.title								|
| content_type_job.field_city_value																| post_jobs.city								|
| content_type_job.field_state_value															| job_states.id => post_jobs.state_id			|
| content_type_job.field_zip_value 																| post_jobs.zip_code							|		
| content_type_job.field_job_reports_to_value													| post_jobs.reports_to							|		
| content_type_job.field_job_direct_reports_value												| post_jobs.of_reports							|		
| content_type_job.field_job_base_pay_value														| post_jobs.salary_range						|		| content_type_job.job_description+content_type_job.field_job_requirements_value				| post_jobs.description							|		
| content_type_job.field_job_status_value					 									| post_jobs.status								|
| content_type_job.field_external_job_board_value												| post_jobs.redirect_to_company_job_board_post	|		
| content_type_job.field_job_expiration_value													| post_jobs.expired_date						|
| content_type_job.field_job_expiration_value													| post_jobs.closed_at							|		
| content_type_job.created																		| post_jobs.created_at							|		
| content_type_job.changed	 																	| post_jobs.updated_at							|		
| content_type_job.field_confidential_value														| post_jobs.post_anonymously 					|
| bf_job_applications.uid																		| job_applications.user_id 						|
| bf_job_applications.nid																		| job_applications.job_id 						|
| bf_job_applications.resume_fid																| job_applications.resume_id 					|
| bf_job_applications.cover_letter_fid															| job_applications.cover_letter_id 				|
| bf_job_applications.additional_info															| job_applications.message 						|
| bf_job_applications.status																	| job_applications.status 						|
+-----------------------------------------------------------------------------------------------+-----------------------------------------------+