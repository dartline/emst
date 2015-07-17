( (
    (NOT rb_employment_categories.need_ill_doc) AND 
    (emst_surgeries.disability != 0) 
  ) OR
  ( (rb_employment_categories.need_ill_doc) AND
    (
      (
        (emst_surgeries.disability = 0) 
      ) OR
      (
        (emst_surgeries.disability = 1) AND 
        NOT rb_clinical_outcomes.can_use_ill_doc_in_ability AND
        ( (emst_surgeries.ill_refused) OR
          (emst_surgeries.ill_doc!="") OR
          (emst_surgeries.ill_doc_new!="") OR
          (emst_surgeries.ill_sertificat) OR
          (emst_surgeries.ill_beg_date!=0) OR
          (emst_surgeries.ill_end_date!=0) OR
          (emst_surgeries.ill_doc_closed)
        )
      ) OR
      (
        (emst_surgeries.disability = 2) AND 
        (emst_surgeries.ill_refused) AND
        (
          (emst_surgeries.ill_doc!="") OR
          (emst_surgeries.ill_doc_new!="") OR
          (emst_surgeries.ill_sertificat) OR
          (emst_surgeries.ill_beg_date!=0) OR
          (emst_surgeries.ill_end_date!=0) OR
          (emst_surgeries.ill_doc_closed)
        )
      ) OR
      (
        (emst_surgeries.disability = 2) AND
        ( (emst_surgeries.clinical_outcome_id IS NULL) OR
          (emst_surgeries.clinical_outcome_id=0) 
        ) AND
        ( NOT emst_surgeries.ill_refused ) AND
        ( (emst_surgeries.ill_doc!="") = (emst_surgeries.ill_sertificat)) AND
        ( emst_surgeries.ill_beg_date=0 OR 
          emst_surgeries.ill_end_date=0 OR 
          ( (emst_surgeries.ill_beg_date>emst_surgeries.ill_end_date) OR 
            DATEDIFF(emst_surgeries.ill_end_date, emst_surgeries.ill_beg_date)>30
          )
        )
      )
    )
  )
)