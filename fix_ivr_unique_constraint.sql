-- Fix IVR DTMF duplicate issue by adding UNIQUE constraint
-- This prevents multiple actions with the same DTMF digit in the same IVR menu

-- First, remove any existing duplicate entries (keep the first one)
DELETE t1 FROM ivr_actions t1
INNER JOIN ivr_actions t2
WHERE
    t1.id > t2.id AND
    t1.ivr_menu_id = t2.ivr_menu_id AND
    t1.dtmf_digit = t2.dtmf_digit;

-- Add UNIQUE constraint
ALTER TABLE ivr_actions
ADD UNIQUE KEY unique_menu_dtmf (ivr_menu_id, dtmf_digit);
