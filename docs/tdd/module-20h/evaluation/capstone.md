# Capstone · Jours spéciaux et blocage de la programmation

## Contexte

Campus Planning affiche une semaine type. Les professeurs programment des cours récurrents, et le calendrier montre la semaine courante.

L'administration veut pouvoir déclarer des jours spéciaux: une date et un libellé. Un jour spécial ne peut pas accueillir de nouveau cours. Les professeurs doivent voir le jour spécial dans le calendrier et être empêchés de programmer ou de déplacer un cours ce jour-là.

Votre mission: ajouter cette fonctionnalité en TDD, sans écrire de code de production avant un test rouge.

## Fonctionnalité

1. Un administrateur peut ajouter un jour spécial avec une date et un libellé.
2. Un administrateur peut supprimer un jour spécial.
3. Seuls les administrateurs accèdent à la page de gestion.
4. Un professeur ne peut pas programmer un cours sur un jour spécial de la semaine affichée.
5. Un professeur ne peut pas déplacer un cours vers un jour spécial.
6. Le calendrier affiche le libellé du jour spécial.
7. Un jour spécial situé la semaine suivante ne bloque pas la semaine courante.

## Règles d'acceptation

1. **Rôle admin**: le rôle `Admin` existe. Un professeur et un étudiant reçoivent une réponse 403 sur la page d'administration.
2. **Création**: l'admin ajoute une date unique, aujourd'hui ou plus tard, avec un libellé obligatoire.
3. **Suppression**: l'admin supprime un jour spécial. Les cours existants ne sont pas supprimés.
4. **Programmation**: la création d'un cours sur un jour spécial de la semaine affichée échoue avec une erreur sur le champ `dayOfWeek`.
5. **Modification**: le déplacement d'un cours vers un jour spécial de la semaine affichée échoue de la même façon.
6. **Calendrier**: le jour spécial apparaît dans l'en-tête de la colonne, avec son libellé.
7. **Portée**: le contrôle utilise la semaine affichée, pas toutes les semaines. Un jour spécial la semaine prochaine ne bloque pas cette semaine.
8. **Cours existants**: un cours déjà programmé sur un jour spécial reste visible. La règle bloque la programmation et le déplacement, pas la consultation.

## Contrat technique

- Rôle: `App\Enums\Role::Admin`, `User::isAdmin()`.
- Autorisation: gate `manage-special-days`.
- Modèle: `App\Models\SpecialDay`, migration `special_days`:
  - `date` (date, unique);
  - `label` (string);
  - `created_by` (foreign key nullable vers `users`).
- Page admin: route `admin.special-days`, composant Livewire `pages::special-days`.
- Calendrier: composant `pages::calendar`, computed `specialDays`, validation dans `saveCourse()`.
- Tests:
  - `tests/Feature/Admin/SpecialDayTest.php`
  - `tests/Feature/SpecialDayCourseTest.php`
- Les règles d'architecture vont dans `tests/Unit/ArchTest.php`.

## Contraintes TDD

- Pas de code de production sans un test qui échoue.
- Un test, un comportement.
- Le test doit échouer pour la bonne raison, pas à cause d'une erreur de syntaxe.
- Le refactor ne modifie pas les tests.
- Aucun email réel, aucune requête réseau, aucun `sleep`.
- Utiliser `RefreshDatabase`, les factories et `Notification::fake()` si la notification est touchée.
- Geler le temps avec `travelTo()` pour rendre les semaines déterministes.
- Ne pas modifier les tests existants pour faire passer le capstone.

## Livrables

1. `tests/Feature/Admin/SpecialDayTest.php` et `tests/Feature/SpecialDayCourseTest.php`.
2. Le modèle, la migration, la factory et la page d'administration.
3. Le rôle admin, la gate et le lien de navigation.
4. L'intégration au calendrier: badge, validation, options désactivées.
5. `journal-tdd.md` avec au moins 8 cycles:
   - comportement visé;
   - test rouge et message d'échec;
   - code minimal ajouté;
   - refactor éventuel.
6. Cinq règles dans `tests/Unit/ArchTest.php`, chacune justifiée en une phrase.
7. Une CI verte: `composer test`.

## Jalons

| Moment | Attendu |
| --- | --- |
| Session 8 | Test d'acceptance du jour spécial, rouge, et liste des tests internes |
| Session 9 | Rôle admin, page d'administration et premiers tests verts |
| Session 10 | Blocage de la programmation, badge calendrier, journal, CI verte, démonstration |

## Démonstration

8 minutes par binôme, puis 2 minutes de questions.

Montrer:

1. Un cycle rouge, vert, refactor tiré du journal.
2. La page admin et l'ajout d'un jour spécial.
3. Le refus de programmer un cours ce jour-là.
4. Le test qui prouve que la semaine suivante n'est pas bloquée.
5. Le badge dans le calendrier.
6. La CI verte.

## Grille de notation

| Critère | Points | Attendus |
| --- | --- | --- |
| Fonctionnalité | 4 | Admin, CRUD, blocage création et modification, badge, portée de la semaine |
| Tests d'abord | 5 | Le journal montre des tests rouges avant le code, pour chaque comportement |
| Qualité des tests | 3 | Noms clairs, un comportement par test, temps gelé, pas de dépendance à l'implémentation |
| Conception et refactor | 3 | Le code reste lisible, les responsabilités sont séparées, les tests n'ont pas été modifiés pour refactorer |
| Architecture | 2 | Cinq règles utiles, justifiées, sans exception abusive |
| Journal TDD | 2 | Cycles complets, échecs expliqués, décisions documentées |
| CI | 1 | `composer test` passe |
| **Total** | **20** | |

## Pénalités

- Code de production écrit avant le test: jusqu'à 3 points.
- Tests qui ne peuvent pas échouer: jusqu'à 3 points.
- Test qui dépend de la date réelle: 1 point.
- Page admin accessible à un professeur: 2 points.
- Tests modifiés pendant un refactor: 2 points.
- CI rouge: 1 point, et la soutenance doit expliquer pourquoi.

## Conseils

- Commencez par le test d'acceptance du refus de programmation. C'est le comportement qui donne du sens à toute la fonctionnalité.
- Le piège principal est la portée: un jour spécial est une date, pas un jour de la semaine. Le contrôle doit utiliser la semaine affichée.
- Pour la page admin, testez d'abord le 403 d'un professeur, puis le 200 d'un admin.
- La validation de la date doit utiliser `whereDate` ou un cast qui stocke `Y-m-d`, sinon SQLite compare `2026-11-11` et `2026-11-11 00:00:00` et laisse passer les doublons.
- Écrivez le journal au fur et à mesure. Un journal reconstruit après coup se voit.
