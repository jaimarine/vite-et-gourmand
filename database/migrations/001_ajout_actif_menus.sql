
-- ============================================
-- PROJET : VITE & GOURMAND
-- ÉVOLUTION DE LA TABLE MENUS
-- ============================================

-- Ajout d'un état actif/inactif.
-- Les menus existants restent actifs.
ALTER TABLE menus
ADD COLUMN actif BOOLEAN NOT NULL DEFAULT TRUE;
