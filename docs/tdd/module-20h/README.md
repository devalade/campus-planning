# TDD en pratique · 20 heures

Module de 20 heures pour apprendre le Test-Driven Development avec PHP, Laravel et Pest.

La promesse est simple: à la fin, vous n'ajoutez plus des tests après le code. Vous écrivez le test qui décrit le comportement, vous le regardez échouer, puis vous laissez le design émerger.

## Public

Licence 3 ou Master 1 en développement web. Les étudiants savent déjà:

- écrire du PHP et une application Laravel simple;
- utiliser Git, un terminal et Composer;
- lire et modifier des tests existants.

Aucune expérience de TDD n'est nécessaire.

## Objectifs

À la fin du module, l'étudiant est capable de:

1. Expliquer la boucle rouge, vert, refactor et les trois lois du TDD.
2. Écrire un test qui échoue avant la première ligne de code de production.
3. Faire passer un test avec le minimum de code, puis refactorer sans changer le comportement.
4. Découper un problème en liste de comportements testables.
5. Concevoir des tests lisibles avec Arrange, Act, Assert.
6. Choisir le bon double de test: stub, spy, mock ou fake.
7. Tester une fonctionnalité Laravel: HTTP, base de données, validation, autorisation.
8. Tester un composant Livewire et ses effets de bord.
9. Utiliser les tests d'architecture pour protéger les frontières du code.
10. Livrer une fonctionnalité test-first avec une CI verte et un journal de cycles.

## Format

- 10 sessions de 2 heures, soit 20 heures.
- Chaque session: 20 minutes de théorie, 70 minutes de pratique, 20 minutes de débrief.
- La pratique se fait en binôme, avec un pilote et un navigateur qui alternent à chaque cycle.
- Le fil rouge est l'application Campus Planning de ce dépôt.

Le module peut aussi être compressé en 5 demi-journées de 4 heures. Dans ce cas, garder l'ordre des sessions et fusionner les débriefs.

## Évaluation

| Part | Poids | Contenu |
| --- | --- | --- |
| Exercices hebdomadaires | 40% | Un test rouge, un vert, un refactor par semaine. Qualité des tests et régularité. |
| Capstone | 50% | Fonctionnalité, tests, journal TDD, règles d'architecture, CI verte. |
| Participation | 10% | Quiz, revues de code, débriefs. |

Le détail du capstone et la grille sont dans [evaluation/capstone.md](evaluation/capstone.md).

## Parcours

| Session | Sujet | Pratique |
| --- | --- | --- |
| 1 | Pourquoi TDD, la boucle, les trois lois | String Calculator, premiers cycles |
| 2 | PHPUnit, Pest et anatomie d'un test | Réécrire un test, créer `SlotTest` |
| 3 | Baby steps, triangulation, liste de tests | Chiffres romains ou extension de `Slot` |
| 4 | Conception des tests, fixtures, factories | Builder de payload de cours |
| 5 | Doubles de test, temps et hasard | Notificateur de cours |
| 6 | TDD Laravel: HTTP, base, validation | Création d'un cours |
| 7 | TDD Livewire: composants et effets | Modale de création, notifications |
| 8 | Outside-in, double boucle, refactoring | Début du capstone |
| 9 | Architecture tests et CI | Règles `arch()` et suite verte |
| 10 | Jours spéciaux et soutenance | Démonstration et revue |

Le détail de chaque session est dans [programme.md](programme.md).

## Outils

- PHP 8.4
- Laravel 13
- Pest 5
- Livewire 4
- SQLite
- Git

Commandes:

```bash
composer setup
php artisan test --compact
php artisan test --compact tests/Unit/SlotTest.php
php artisan test --compact --filter=un_professeur
vendor/bin/pint --dirty
```

## Supports

- [Présentation du module](presentation/index.html): 26 diapositives, navigation clavier, export PDF.
- [Design du deck](presentation/DESIGN.md): grille, typographie, budgets et vérification.
- [Programme détaillé](programme.md): déroulé des 10 sessions.
- [Capstone](evaluation/capstone.md): brief, jalons, grille.
- [Quiz de vérification](evaluation/quiz.md): 10 questions formatives.
- [Journal TDD](evaluation/journal-tdd-template.md): modèle à remplir à chaque cycle.
- [Tests d'architecture avec Pest](../../pest/arch-testing/README.md): support de la session 9.

## Règle du module

Un test qui passe du premier coup ne prouve rien. Avant de coder, vérifiez qu'il échoue pour la bonne raison.
