from pathlib import Path
base = Path("/buts4/www/nafy-application/assets/vue")
accueil_path = base / "views/AccueilView.vue"
accueil = accueil_path.read_text(encoding="utf-8")
old_form = accueil_path.read_text(encoding="utf-8").split("            <form class=\"calendrier__ajout\"")[1].split("            </form>")[0]
