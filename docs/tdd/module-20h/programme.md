# Programme détaillé · 10 sessions de 2 heures

Chaque session suit le même rythme:

- 20 minutes de théorie;
- 70 minutes de pratique en binôme;
- 20 minutes de débrief et de mise au propre.

Le pilote écrit le test et le code. Le navigateur relit, pose les questions et tient la liste de tests. Les rôles changent à chaque cycle.

## Session 1 · Pourquoi TDD

### Objectifs

- Expliquer le coût du changement dans un logiciel.
- Distinguer un test de vérification d'un test de conception.
- Appliquer la boucle rouge, vert, refactor sur un petit problème.
- Écrire un test qui échoue pour la bonne raison.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-20 | Théorie | Le problème du code qui marche mais ne change plus. Définition de TDD. La boucle. |
| 20-40 | Démonstration | String Calculator: premier test, premier échec, première classe. |
| 40-100 | Pratique | En binôme, ajouter les comportements 2 à 5. Chaque cycle est noté dans le journal. |
| 100-120 | Débrief | Comparer les test lists. Repérer les tests qui passent du premier coup. |

### Exercice: String Calculator

Comportements, dans cet ordre:

1. `add('')` retourne `0`.
2. `add('1')` retourne `1`.
3. `add('1,2')` retourne `3`.
4. `add("1\n2,3")` retourne `6`.
5. `add('//;\n1;2')` retourne `3`.
6. `add('-1')` lève une exception qui nomme le nombre négatif.

Règles:

- Un comportement à la fois.
- Le test doit échouer avant de coder.
- Le code de production le plus simple possible.
- Pas de `if` anticipé pour un comportement qui n'a pas encore de test.

### Livrable

- `tests/Unit/StringCalculatorTest.php`
- `app/Domain/StringCalculator.php`
- Trois cycles dans `journal-tdd.md`

### Devoir

Terminer les nombres négatifs. Pour chaque cycle, noter la raison exacte de l'échec.

## Session 2 · PHPUnit, Pest et anatomie d'un test

### Objectifs

- Expliquer que Pest s'appuie sur PHPUnit.
- Passer d'une assertion PHPUnit à une expectation Pest.
- Écrire un test lisible avec Arrange, Act, Assert.
- Nommer un test par le comportement attendu.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-20 | Théorie | Pest comme couche au-dessus de PHPUnit. Assertions et expectations. AAA. |
| 20-35 | Démonstration | Le même test en PHPUnit puis en Pest. `expect()->toBe()` et `Assert::assertSame()`. |
| 35-95 | Pratique | Réécrire un test PHPUnit, puis créer `SlotTest` avec trois comportements. |
| 95-120 | Débrief | Comparer les noms de tests. Repérer les assertions qui testent l'implémentation. |

### Exercice: le créneau

Créer un value object `Slot` avec:

1. `overlaps()` retourne `false` pour deux créneaux qui se touchent.
2. `overlaps()` retourne `true` pour deux créneaux qui se chevauchent.
3. `overlaps()` retourne `false` pour deux jours différents.

Contraintes:

- Commencer par le test.
- Utiliser `Weekday`, déjà présent dans l'application.
- Ne pas extraire la logique du composant Livewire avant que les tests soient verts.

### Livrable

- `tests/Unit/SlotTest.php`
- `app/Domain/Slot.php`
- Un test PHPUnit réécrit en Pest

### Devoir

Ajouter les cas limites: mêmes heures de début, mêmes heures de fin, créneau qui contient l'autre.

## Session 3 · Baby steps et triangulation

### Objectifs

- Découper un problème en pas plus petits.
- Utiliser le faux code et la triangulation quand la formule n'est pas évidente.
- Reconnaître le moment où il faut généraliser.
- Tenir une liste de tests vivante.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-20 | Théorie | Fake it, triangulation, obvious implementation. Pourquoi le faux code est une étape. |
| 20-35 | Démonstration | Ajouter `duration()` à `Slot`, d'abord avec un faux retour. |
| 35-95 | Pratique | Chiffres romains en binôme, ou extension de `Slot`. Cinq cycles minimum. |
| 95-120 | Débrief | Comparer les triangulations. Repérer les raccourcis qui ont cassé un test. |

### Exercice: chiffres romains

Comportements:

1. `1` retourne `I`.
2. `2` retourne `II`.
3. `3` retourne `III`.
4. `4` retourne `IV`.
5. `5` retourne `V`.
6. `9` retourne `IX`.
7. `40` retourne `XL`.
8. `1994` retourne `MCMXCIV`.

Règle: ne pas écrire la table complète des symboles dès le premier test. Laisser la table émerger des cas.

### Livrable

- `tests/Unit/RomanNumeralTest.php`
- `app/Domain/RomanNumeral.php`
- Cinq cycles dans le journal, avec le test rouge et le code minimal

### Devoir

Refactorer la conversion. Les tests ne doivent pas changer.

## Session 4 · Concevoir des tests solides

### Objectifs

- Écrire un test qui survit à un refactor.
- Construire des données de test avec des factories et des builders.
- Éviter les tests fragiles: trop de détails, trop d'assertions, trop de logique.
- Tester un comportement, pas une méthode privée.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-20 | Théorie | Un comportement par test. AAA. Fixtures, factories, builders. Tests fragiles et tests durables. |
| 20-35 | Démonstration | Transformer un payload de cours en builder lisible. |
| 35-95 | Pratique | Ajouter des tests de validation au formulaire de cours avec un builder. |
| 95-120 | Débrief | Revue croisée: chaque binôme lit un test et propose une simplification. |

### Exercice: le builder de cours

Créer `tests/Support/CoursePayload.php` avec des méthodes:

- `forProfessor(User $professor)`
- `withTitle(string $title)`
- `inRoom(Room $room)`
- `on(Weekday $day)`
- `from(string $start, string $end)`
- `withStudents(User ...$students)`
- `toArray()`

Utiliser le builder dans au moins trois tests de validation.

### Livrable

- `tests/Support/CoursePayload.php`
- Trois tests de validation qui utilisent le builder
- Une note de cinq lignes: pourquoi le builder rend les tests plus robustes

### Devoir

Ajouter les cas: titre vide, salle inconnue, jour du dimanche, heure de fin avant le début.

## Session 5 · Les doubles de test

### Objectifs

- Distinguer dummy, stub, spy, mock et fake.
- Choisir un fake quand une vraie implémentation légère suffit.
- Tester le temps, le hasard et les effets de bord sans rendre la suite instable.
- Vérifier un résultat plutôt qu'une chorégraphie.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-20 | Théorie | Les cinq doubles. London contre Chicago. Fakes Laravel. Temps et hasard. |
| 20-35 | Démonstration | `CourseNotifier`: professeur et étudiants, avec `Notification::fake()`. |
| 35-95 | Pratique | Écrire le service et ses tests, y compris le cas d'échec d'envoi. |
| 95-120 | Débrief | Repérer les mocks inutiles. Proposer un fake à la place. |

### Exercice: le notificateur

Créer `app/Services/CourseNotifier.php`:

- `sendCreated(Course $course): void`
- envoie `CourseScheduleNotification` au professeur et aux étudiants inscrits;
- si l'envoi échoue, journalise l'erreur et ne supprime pas le cours.

Tests:

1. Le professeur reçoit la notification.
2. Chaque étudiant inscrit la reçoit.
3. Un cours sans étudiant ne notifie que le professeur.
4. Un échec d'envoi est journalisé.
5. Le cours existe toujours après l'échec.

### Livrable

- `app/Services/CourseNotifier.php`
- `tests/Unit/CourseNotifierTest.php`
- Une décision écrite: pourquoi un fake plutôt qu'un mock pour les notifications

### Devoir

Ajouter un test avec `Queue::fake()` pour vérifier qu'une notification queued est bien envoyée.

## Session 6 · TDD dans Laravel

### Objectifs

- Écrire un feature test qui décrit un contrat HTTP.
- Utiliser les factories pour construire des données valides.
- Tester la validation, l'autorisation et les effets en base.
- Isoler chaque test avec `RefreshDatabase`.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-20 | Théorie | Feature test, requête, réponse, effet. Ce qu'on teste et ce qu'on ne teste pas. |
| 20-35 | Démonstration | Premier test de création d'un cours, rouge. |
| 35-95 | Pratique | Écrire les tests de création, validation et autorisation. |
| 95-120 | Débrief | Lire le test de référence `CourseCreationTddTest.php` et comparer. |

### Exercice: création d'un cours

Comportements:

1. Un professeur crée un cours et inscrit les étudiants.
2. Un étudiant ne peut pas créer un cours.
3. Le titre est obligatoire.
4. La salle doit être valide.
5. Le jour doit être planifiable.
6. L'heure de début précède l'heure de fin.

Utiliser `CoursePayload` de la session 4.

### Livrable

- `tests/Feature/CourseCreationTest.php`
- Le code nécessaire pour faire passer les tests
- Aucun test qui dépend de l'ordre d'exécution

### Devoir

Ajouter le test du conflit de créneau et celui des créneaux qui se touchent.

## Session 7 · TDD dans Livewire

### Objectifs

- Tester un composant Livewire comme un utilisateur.
- Vérifier l'état, les erreurs et les effets de bord.
- Tester les notifications et les queues sans envoyer d'email.
- Distinguer un test de composant d'un test de navigateur.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-20 | Théorie | `Livewire::test()`, état, actions, assertions. Ce que le test ne couvre pas. |
| 20-35 | Démonstration | Ouvrir la modale, saisir, soumettre, observer. |
| 35-95 | Pratique | Tester la création, la validation et la notification depuis le composant. |
| 95-120 | Débrief | Comparer avec `CourseScheduleTest.php`. Repérer les assertions d'état fragiles. |

### Exercice: la modale de cours

Tests:

1. La modale s'ouvre.
2. La modale se ferme après une création valide.
3. Les erreurs de validation restent dans le composant.
4. Le professeur et les étudiants reçoivent la notification.
5. Un échec d'email affiche un avertissement et garde le cours.

### Livrable

- `tests/Feature/CourseModalTest.php`
- Le code du composant
- Un test de notification avec `Notification::fake()`

### Devoir

Ajouter un test pour l'annulation de la modale sans création.

## Session 8 · Outside-in et refactoring

### Objectifs

- Écrire un test d'acceptance avant les tests unitaires.
- Descendre dans la double boucle.
- Construire un walking skeleton.
- Refactorer sans modifier les tests.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-20 | Théorie | Double boucle, walking skeleton, moment où l'on descend dans le domaine. |
| 20-35 | Démonstration | Test d'acceptance d'un jour spécial. |
| 35-95 | Pratique | Écrire le test d'acceptance du capstone, puis les premiers tests internes. |
| 95-120 | Débrief | Vérifier que le test externe échoue pour la bonne raison. |

### Exercice: début du capstone

Écrire le test d'acceptance:

```php
test('un professeur ne peut pas programmer un cours un jour spécial', function () {
    // ...
});
```

Puis la liste des tests internes:

- un admin peut ajouter un jour spécial;
- un professeur ne peut pas accéder à la page d'administration;
- un professeur ne peut pas programmer un cours ce jour-là;
- le calendrier affiche le libellé du jour spécial;
- la semaine suivante n'est pas bloquée.

### Livrable

- Le test d'acceptance rouge
- La liste des tests internes
- Un walking skeleton qui répond sans logique métier

### Devoir

Implémenter un comportement interne en TDD, puis revenir au test d'acceptance.

## Session 9 · Architecture tests et CI

### Objectifs

- Écrire une règle `arch()` utile.
- Utiliser les presets et les modificateurs avec discernement.
- Lire un échec de test d'architecture.
- Garder une suite rapide et une CI verte.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-20 | Théorie | Tests de comportement contre tests de forme. Presets, wildcards, exceptions. |
| 20-35 | Démonstration | Ajouter une règle, la voir échouer, corriger le code. |
| 35-95 | Pratique | Ajouter cinq règles au projet, corriger les violations, lancer la CI. |
| 95-120 | Débrief | Chaque règle doit avoir une justification en une phrase. |

### Exercice: le contrat d'architecture

Ajouter des règles pour:

1. Les modèles ne dépendent pas de la couche HTTP.
2. Les enums ne dépendent d'aucun framework.
3. Les notifications exposent `toMail()`.
4. Les contrôleurs sont `final` et suffixés `Controller`.
5. Aucun `dd`, `dump`, `die` dans `App`.

Lancer:

```bash
php artisan test --compact tests/Unit/ArchTest.php
composer test
```

### Livrable

- `tests/Unit/ArchTest.php` avec cinq règles
- Une note par règle: ce qu'elle protège et pourquoi
- CI verte

### Devoir

Ajouter un preset `security` et expliquer chaque exception avec `ignoring()`.

## Session 10 · Capstone et soutenance

### Objectifs

- Terminer la fonctionnalité test-first.
- Démontrer le cycle rouge, vert, refactor.
- Justifier les choix de conception et les règles d'architecture.
- Faire une rétrospective honnête.

### Déroulé

| Temps | Activité | Contenu |
| --- | --- | --- |
| 0-50 | Atelier | Finaliser, lancer la CI, préparer la démonstration. |
| 50-110 | Soutenances | 8 minutes de démonstration et 2 minutes de questions par binôme. |
| 110-120 | Rétrospective | Ce qui a changé dans la façon d'écrire du code. |

### Livrables

- La fonctionnalité de gestion des jours spéciaux et le blocage de la programmation.
- Les tests de feature et les tests unitaires.
- Le journal TDD complet.
- Les règles d'architecture.
- Une CI verte.

### Grille

Voir [evaluation/capstone.md](evaluation/capstone.md).

## Règles communes

- Pas de code de production sans un test rouge.
- Un test ne vérifie qu'un comportement.
- Un test doit échouer pour la bonne raison.
- Le refactor ne modifie pas les tests.
- Les tests doivent pouvoir tourner dans n'importe quel ordre.
- Aucun test ne dépend du réseau, de l'horloge réelle ou du hasard non contrôlé.
