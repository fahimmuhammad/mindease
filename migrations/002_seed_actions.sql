-- 002_seed_actions.sql
-- Seed a small set of core activities for MindEase

INSERT INTO actions (slug, title, type, duration_seconds, content_json)
VALUES
  ('box_breathing_2m', 'Box Breathing — 2 minutes', 'breathing', 120,
    JSON_ARRAY(
      JSON_OBJECT('step',1,'text','Find a comfortable seat.'),
      JSON_OBJECT('step',2,'text','Inhale for 4 seconds.'),
      JSON_OBJECT('step',3,'text','Hold for 4 seconds.'),
      JSON_OBJECT('step',4,'text','Exhale for 4 seconds.'),
      JSON_OBJECT('step',5,'text','Hold for 4 seconds. Repeat for 2 minutes.')
    )
  ),
  ('grounding_543', 'Grounding — 5-4-3-2-1', 'grounding', 180,
    JSON_ARRAY(
      JSON_OBJECT('step',1,'text','Name 5 things you can see.'),
      JSON_OBJECT('step',2,'text','Name 4 things you can touch.'),
      JSON_OBJECT('step',3,'text','Name 3 things you can hear.'),
      JSON_OBJECT('step',4,'text','Name 2 things you can smell (or would like to).'),
      JSON_OBJECT('step',5,'text','Name 1 positive thing about yourself.')
    )
  ),
  ('micro_journal_1', 'Micro-Journal — 1 sentence', 'journal', 120,
    JSON_ARRAY(
      JSON_OBJECT('step',1,'text','Write one sentence describing what is bothering you.'),
      JSON_OBJECT('step',2,'text','If helpful, write one small next step to try.')
    )
  ),
  ('short_walk_5m', 'Short Walk — 5 minutes', 'other', 300,
    JSON_ARRAY(
      JSON_OBJECT('step',1,'text','Stand up and step outside or walk around your space for 5 minutes.'),
      JSON_OBJECT('step',2,'text','Breathe deeply and notice sensations in your feet.')
    )
  );

-- id auto-increment continues; add more seeds as needed
