(() => {
  'use strict';

  const esc = (value) => String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;');
  const ic = (value) => `<code class="ic">${esc(value)}</code>`;

  const tokenPattern = /('(?:[^'\\]|\\.)*'|"(?:[^"\\]|\\.)*")|(\/\/[^\n]*)|(\$[A-Za-z_]\w*)|\b(arch|expect|test|it|not|preset|ignoring|classes|interfaces|traits|enums|extending|implementing|using|abstracts|custom|toUse|toOnlyUse|toUseNothing|toOnlyBeUsedIn|toBeUsed|toBeUsedIn|toBeFinal|toBeClasses|toBeInterfaces|toBeEnums|toBeTraits|toBeReadonly|toBeAbstract|toBeCasedCorrectly|toExtend|toExtendNothing|toImplement|toImplementNothing|toUseTrait|toUseTraits|toHaveMethod|toHaveMethods|toHaveAttribute|toHavePrefix|toHaveSuffix|toHaveLineCountLessThan|toHaveConstructor|toHaveDestructor|toHaveMethodsDocumented|toHavePropertiesDocumented|toHavePrivateMethods|toHaveProtectedMethods|toHavePublicMethods|toBe|toEqual|toBeTrue|toBeFalse|toBeNull|toBeEmpty|toContain|toHaveCount|toHaveKey|toHaveKeys|toBeInstanceOf|toBeArray|toBeString|toBeInt|toBeFloat|toBeBool|toBeObject|toBeCallable|toBeIterable|toBeScalar|toBeList|toHaveLength|toHaveSameSize|toStartWith|toEndWith|toMatch|toMatchArray|toMatchConstraint|toBeFile|toBeDirectory|toBeReadableFile|toBeWritableFile|toBeReadableDirectory|toBeWritableDirectory|toBeGreaterThan|toBeGreaterThanOrEqual|toBeLessThan|toBeLessThanOrEqual|toEqualCanonicalizing|toEqualWithDelta|toHaveProperty|toHaveProperties|toThrow|toBeJson|toBeUuid|toBeUlid|toBeEmail|toBeUrl|toBeIn|toBeBetween|php|security|laravel|strict|relaxed)\b|(::class|->)/g;

  const highlight = (source) => esc(source).replace(
    tokenPattern,
    (match, string, comment, variable, keyword, punctuation) => {
      if (string) return `<span class="tok-str">${string}</span>`;
      if (comment) return `<span class="tok-com">${comment}</span>`;
      if (variable) return `<span class="tok-var">${variable}</span>`;
      if (keyword) return `<span class="tok-fn">${keyword}</span>`;
      if (punctuation) return `<span class="tok-op">${punctuation}</span>`;
      return match;
    },
  );

  const codeCard = (label, source, options = {}) => {
    const clean = String(source).replace(/^\n+|\n+$/g, '');
    const lines = highlight(clean).split('\n');

    return `<div class="code-card ${options.className || ''}">
      <div class="code-head"><span class="code-dot"></span><span>${esc(label)}</span></div>
      <pre class="code"><code>${lines.map((line, index) => `<span class="code-line"><span class="code-num">${String(index + 1).padStart(2, '0')}</span><span class="code-src">${line || ' '}</span></span>`).join('')}</code></pre>
    </div>`;
  };

  const compareTable = (rows) => `<div class="table-wrap">
    <table class="cmp">
      <thead><tr><th>PHPUnit</th><th>Pest</th><th>Ce que ça vérifie</th></tr></thead>
      <tbody>${rows.map(([phpunit, pest, label]) => `<tr><td><code>${esc(phpunit)}</code></td><td><code>${esc(pest)}</code></td><td>${label}</td></tr>`).join('')}</tbody>
    </table>
  </div>`;

  const titleBlock = (kicker, title, lede = '', compact = false) => `<div class="titleblock${compact ? ' compact' : ''}">
    <p class="kicker"><span></span>${kicker}</p>
    <h2>${title}</h2>
    ${lede ? `<p class="lede">${lede}</p>` : ''}
  </div><!--slide-content-->`;

  const slides = [
    {
      layout: 'cover',
      chrome: false,
      title: 'Test-Driven Development',
      html: `<div class="cover">
        <div class="cover-top">
          <span class="wordmark"><span class="wordmark-mark"></span>TDD / PEST</span>
          <span class="cover-meta">MODULE 20 HEURES · LICENCE</span>
        </div>

        <div class="cover-main">
          <div class="cover-copy">
            <p class="kicker"><span></span>DU TEST AU DESIGN</p>
            <h1>Test-Driven<br><em>Development.</em></h1>
            <p class="cover-lede">Écrire le test avant le code. Utiliser l'échec comme spécification. Laisser les tests guider le design.</p>
            <div class="cover-chips">
              <span>10 sessions</span>
              <span>20 heures</span>
              <span>Pest 5</span>
              <span>Laravel 13</span>
            </div>
          </div>

          <div class="cover-visual">
            ${codeCard('tests/Unit/SlotTest.php', `test('deux créneaux qui se touchent', function () {
    $first = new Slot(Weekday::Monday, '08:00', '10:00');
    $second = new Slot(Weekday::Monday, '10:00', '12:00');

    expect($first->overlaps($second))->toBeFalse();
});`)}
            <p class="cover-caption"><span></span>Le test décrit le comportement avant le code.</p>
          </div>
        </div>

        <div class="cover-foot">
          <span>Campus Planning · Laravel 13</span>
          <span>Rouge. Vert. Refactor.</span>
        </div>
      </div>`,
    },

    {
      section: 'Le problème',
      title: "Le code qui marche n'est pas le code qui dure",
      html: `${titleBlock(
        'LE PROBLÈME',
        "Le code qui marche<br>n'est pas le code qui dure.",
        "Un logiciel peut passer tous les tests et devenir impossible à modifier. TDD attaque ce problème à la source: la conception.",
      )}

      <div class="symptom-grid">
        <article class="symptom">
          <span class="symptom-code">git blame</span>
          <h3>La peur de toucher</h3>
          <p>Chaque modification risque de casser un comportement invisible.</p>
        </article>
        <article class="symptom">
          <span class="symptom-code">assertTrue(true)</span>
          <h3>Les tests après coup</h3>
          <p>Les tests constatent le code écrit. Ils ne le guident pas.</p>
        </article>
        <article class="symptom">
          <span class="symptom-code">3 mois plus tard</span>
          <h3>La régression silencieuse</h3>
          <p>Le bug revient, sans prévenir, dans un chemin que personne ne teste.</p>
        </article>
        <article class="symptom">
          <span class="symptom-code">600 lignes</span>
          <h3>Le design subi</h3>
          <p>Les dépendances s'accumulent. Les classes grossissent. Personne ne décide.</p>
        </article>
      </div>

      <p class="footnote">TDD n'est pas une technique de test. C'est une technique de conception.</p>`,
    },

    {
      layout: 'divider',
      chrome: false,
      title: 'La boucle',
      html: `<div class="divider">
        <span class="divider-numeral" aria-hidden="true">I</span>
        <div class="divider-copy">
          <p class="divider-kicker">PARTIE 01 · SESSIONS 1 À 3</p>
          <h2>La boucle</h2>
          <p class="divider-line">Rouge. Vert. Refactor.</p>
        </div>
        <span class="divider-meta">Le rythme avant la perfection</span>
      </div>`,
    },

    {
      section: 'La boucle',
      title: 'TDD est une boucle de conception',
      html: `${titleBlock(
        'DÉFINITION',
        'TDD est une boucle de conception.',
        "Le test est la première utilisation du code. Il révèle son interface avant son implémentation.",
      )}

      <div class="loop loop--lg">
        <div class="loop-step step-red">
          <span>01 · ROUGE</span>
          <h3>Écrire le test</h3>
          <p>Le comportement est absent. Le test échoue pour la bonne raison.</p>
        </div>
        <span class="loop-arrow" aria-hidden="true">→</span>
        <div class="loop-step step-green">
          <span>02 · VERT</span>
          <h3>Faire passer</h3>
          <p>Le code le plus simple qui rend le test vert. Rien de plus.</p>
        </div>
        <span class="loop-arrow" aria-hidden="true">→</span>
        <div class="loop-step step-refactor">
          <span>03 · REFACTOR</span>
          <h3>Améliorer</h3>
          <p>Supprimer la duplication, clarifier les noms, extraire les objets.</p>
        </div>
      </div>

      <blockquote class="pullquote">La boucle dure des secondes. Si elle dure des heures, le pas est trop grand.</blockquote>`,
    },

    {
      section: 'La boucle',
      title: 'Les trois lois du TDD',
      html: `${titleBlock(
        'LE GARDE-FOU',
        'Les trois lois du TDD.',
        "Elles n'imposent pas une religion. Elles imposent un rythme: un pas petit, vérifiable, à la fois.",
      )}

      <div class="laws">
        <div class="law"><span class="law-num">1</span><p>Pas de code de production sans un test qui échoue.</p></div>
        <div class="law"><span class="law-num">2</span><p>Pas plus de test qu'il n'en faut pour échouer.</p></div>
        <div class="law"><span class="law-num">3</span><p>Pas plus de code qu'il n'en faut pour passer.</p></div>
      </div>

      <p class="footnote">Formulées par Robert C. Martin. Si vous ne pouvez pas écrire le test, vous ne savez pas encore ce que vous voulez construire.</p>`,
    },

    {
      section: 'La boucle',
      title: 'Premier cycle: le créneau',
      html: `${titleBlock('EXEMPLE · ROUGE', 'Le premier test échoue pour la bonne raison.', 'Un seul comportement, un seul échec, une raison claire.', true)}

      <div class="split split-45-55">
        <div class="points">
          <div class="point"><span>01</span><p>Deux créneaux qui se touchent ne se chevauchent pas.</p></div>
          <div class="point"><span>02</span><p>Deux créneaux qui se chevauchent se détectent.</p></div>
          <div class="point"><span>03</span><p>Deux jours différents ne se chevauchent jamais.</p></div>
        </div>
        ${codeCard('tests/Unit/SlotTest.php', `test('deux créneaux qui se touchent', function () {
    $first = new Slot(Weekday::Monday, '08:00', '10:00');
    $second = new Slot(Weekday::Monday, '10:00', '12:00');

    expect($first->overlaps($second))->toBeFalse();
});`)}
      </div>

      <div class="failure">
        <span class="failure-label">ÉCHEC ATTENDU</span>
        <code class="failure-title">FAIL  deux créneaux qui se touchent</code>
        <code class="failure-msg">Class "App\\Domain\\Slot" not found</code>
      </div>

      <p class="footnote">Le test échoue parce que la classe n'existe pas. C'est la bonne raison: le comportement n'existe pas encore.</p>`,
    },

    {
      section: 'La boucle',
      title: 'Premier cycle: fake it',
      html: `${titleBlock(
        'EXEMPLE · VERT',
        'Fake it, puis triangulate.',
        'Le premier test ne demande pas la vraie formule. Il demande que le comportement existe.',
        true,
      )}

      <div class="code-pair">
        <div>${codeCard('app/Domain/Slot.php', `final class Slot
{
    public function __construct(
        public Weekday $day,
        public string $start,
        public string $end,
    ) {}

    public function overlaps(Slot $other): bool
    {
        return false;
    }
}`)}</div>
        <div>${codeCard('tests/Unit/SlotTest.php', `test('deux créneaux qui se chevauchent', function () {
    $first = new Slot(Weekday::Monday, '08:00', '10:00');
    $second = new Slot(Weekday::Monday, '09:00', '11:00');

    expect($first->overlaps($second))->toBeTrue();
});`)}</div>
      </div>

      <p class="footnote">Le faux code suffit pour le premier test. Le deuxième test casse le faux code. Le troisième test fixe la frontière.</p>`,
    },

    {
      section: 'La boucle',
      title: 'Premier cycle: la frontière',
      html: `${titleBlock('EXEMPLE · REFACTOR', 'Le troisième test fixe la frontière.', 'Le troisième test transforme une intuition en règle.', true)}

      <div class="split split-55-45">
        ${codeCard('app/Domain/Slot.php', `public function overlaps(Slot $other): bool
{
    return $this->day === $other->day
        && $this->start < $other->end
        && $other->start < $this->end;
}`)}
        <div class="points">
          <div class="point"><span>01</span><p><b>Même jour</b>: deux jours différents ne se chevauchent jamais.</p></div>
          <div class="point"><span>02</span><p><b>Strictement avant</b>: deux créneaux qui se touchent ne se chevauchent pas.</p></div>
          <div class="point"><span>03</span><p><b>Symétrie</b>: la règle fonctionne dans les deux sens.</p></div>
        </div>
      </div>

      <p class="footnote">Chaque ligne est née d'un test. Pas d'anticipation, pas de code spéculatif.</p>`,
    },

    {
      section: 'La boucle',
      title: 'Commencez par la liste de tests',
      html: `${titleBlock(
        'AVANT DE CODER',
        'Commencez par la liste, pas par la classe.',
        "Une liste de comportements rend l'inconnu visible et découpe le problème en pas vérifiables.",
        true,
      )}

      <div class="split split-55-45">
        <div class="rules">
          <div class="rule"><span class="check"></span><p>Un professeur crée un cours.</p></div>
          <div class="rule"><span class="check"></span><p>Un étudiant ne peut pas créer un cours.</p></div>
          <div class="rule"><span class="check"></span><p>Le titre est obligatoire.</p></div>
          <div class="rule"><span class="check"></span><p>Un professeur ne peut pas avoir deux cours qui se chevauchent.</p></div>
          <div class="rule"><span class="check"></span><p>Les étudiants inscrits reçoivent un email.</p></div>
        </div>
        <div class="points">
          <div class="point"><span>01</span><p>Elle rend l'inconnu visible.</p></div>
          <div class="point"><span>02</span><p>Elle découpe le problème en comportements.</p></div>
          <div class="point"><span>03</span><p>Elle sert de plan de session.</p></div>
        </div>
      </div>

      <p class="footnote">Un test, un comportement. Une ligne par comportement.</p>`,
    },

    {
      layout: 'divider',
      chrome: false,
      title: 'La pratique',
      html: `<div class="divider">
        <span class="divider-numeral" aria-hidden="true">II</span>
        <div class="divider-copy">
          <p class="divider-kicker">PARTIE 02 · SESSIONS 4 À 9</p>
          <h2>La pratique</h2>
          <p class="divider-line">Du domaine au composant. Du test au design.</p>
        </div>
        <span class="divider-meta">Doubles · Laravel · Livewire · architecture</span>
      </div>`,
    },

    {
      section: 'Outillage',
      title: 'Pest est une couche au-dessus de PHPUnit',
      html: `${titleBlock(
        'OUTILLAGE',
        'Pest est une couche<br>au-dessus de PHPUnit.',
        "Le moteur d'exécution reste PHPUnit. Pest change la grammaire et l'organisation.",
        true,
      )}

      <div class="split split-55-45">
        <div class="layer-stack" aria-label="Pest s'appuie sur PHPUnit">
          <div class="stack-row stack-tests">
            <span class="stack-tag">VOS TESTS</span>
            <span class="stack-detail">test() · expect() · arch()</span>
          </div>
          <div class="stack-row stack-pest">
            <span class="stack-tag">PEST</span>
            <span class="stack-detail">DSL · expectations · plugins</span>
          </div>
          <div class="stack-row stack-phpunit">
            <span class="stack-tag">PHPUNIT</span>
            <span class="stack-detail">TestCase · Assert · TestRunner</span>
          </div>
        </div>

        <div class="facts">
          <div class="fact"><span class="fact-num">01</span><p>Chaque ${ic('test()')} devient une méthode d'une classe PHPUnit générée.</p></div>
          <div class="fact"><span class="fact-num">02</span><p>${ic('expect($a)->toBe($b)')} finit dans ${ic('Assert::assertSame($b, $a)')}.</p></div>
          <div class="fact"><span class="fact-num">03</span><p>Vos anciens tests PHPUnit continuent de tourner.</p></div>
        </div>
      </div>

      <p class="footnote">Dans ce projet: Pest 5.3.5 et PHPUnit 13.4.5. Le moteur est le même.</p>`,
    },

    {
      section: 'Outillage',
      title: 'La même assertion, deux écritures',
      html: `${titleBlock(
        'MÊME MOTEUR, AUTRE GRAMMAIRE',
        'La même assertion, deux écritures.',
        "Le sujet vient en premier, l'attente ensuite. C'est le changement qui demande le plus d'attention.",
        true,
      )}

      <div class="code-pair">
        <div>${codeCard('PHPUnit', `$this->assertSame(3, $course->students()->count());
$this->assertTrue($course->isFull());
$this->assertNull($course->cancelled_at);
$this->assertContains($student->id, $ids);`)}</div>
        <div>${codeCard('Pest', `expect($course->students()->count())->toBe(3);
expect($course->isFull())->toBeTrue();
expect($course->cancelled_at)->toBeNull();
expect($ids)->toContain($student->id);`, { className: 'code-pest' })}</div>
      </div>

      <p class="footnote">Le rapport d'échec est le même. Seule la lecture change.</p>`,
    },

    {
      section: 'Outillage',
      title: 'Dix assertions, dix équivalents',
      html: `${titleBlock('TABLE DE CORRESPONDANCE', 'Dix assertions, dix équivalents.', 'Les dix assertions du quotidien, avec leur équivalent Pest.', true)}
      ${compareTable([
        ['assertSame($b, $a)', 'expect($a)->toBe($b)', 'identité stricte'],
        ['assertEquals($b, $a)', 'expect($a)->toEqual($b)', 'égalité souple'],
        ['assertTrue($a)', 'expect($a)->toBeTrue()', 'booléen vrai'],
        ['assertCount(3, $a)', 'expect($a)->toHaveCount(3)', 'nombre d\'éléments'],
        ['assertContains($n, $a)', 'expect($a)->toContain($n)', 'présence'],
        ['assertInstanceOf(C::class, $o)', 'expect($o)->toBeInstanceOf(C::class)', 'type'],
        ['assertStringContainsString($n, $s)', 'expect($s)->toContain($n)', 'sous-chaîne'],
        ['assertEqualsCanonicalizing($b, $a)', 'expect($a)->toEqualCanonicalizing($b)', 'tableaux sans ordre'],
        ['assertJson($json)', 'expect($json)->toBeJson()', 'JSON valide'],
        ['$this->expectException(E::class)', 'expect(fn () => ...)->toThrow(E::class)', 'exception'],
      ])}
      <p class="footnote">PHPUnit place l'attendu en premier. Pest place la valeur observée en premier.</p>`,
    },

    {
      section: 'Conception des tests',
      title: 'Un test se lit comme une phrase',
      html: `${titleBlock('CONCEPTION DES TESTS', 'Un test se lit comme une phrase.', 'Trois blocs, aucune logique, un seul comportement.', true)}

      <div class="split split-55-45">
        ${codeCard('tests/Unit/SlotTest.php', `test('deux créneaux qui se chevauchent sont en conflit', function () {
    // Arrange
    $first = new Slot(Weekday::Monday, '08:00', '10:00');
    $second = new Slot(Weekday::Monday, '09:00', '11:00');

    // Act
    $overlaps = $first->overlaps($second);

    // Assert
    expect($overlaps)->toBeTrue();
});`, { className: 'code--sm' })}
        <div class="points">
          <div class="point"><span>01</span><p><b>Un comportement</b> par test.</p></div>
          <div class="point"><span>02</span><p><b>Un nom</b> qui décrit le résultat.</p></div>
          <div class="point"><span>03</span><p><b>Aucune logique</b> dans le test.</p></div>
          <div class="point"><span>04</span><p><b>Le comportement observable</b>, pas l'implémentation.</p></div>
        </div>
      </div>`,
    },

    {
      section: 'Conception des tests',
      title: 'Cinq doubles, cinq intentions',
      html: `${titleBlock(
        'ISOLER',
        'Cinq doubles, cinq intentions.',
        'Un double remplace une dépendance pour rendre le test rapide, déterministe et lisible.',
        true,
      )}

      <div class="table-wrap">
        <table class="cmp">
          <thead><tr><th>Type</th><th>Rôle</th><th>Exemple Laravel</th></tr></thead>
          <tbody>
            <tr><td><code>Dummy</code></td><td>remplit une signature</td><td><code>new NullNotifier()</code></td></tr>
            <tr><td><code>Stub</code></td><td>retourne une valeur</td><td><code>$repo-&gt;shouldReceive('count')-&gt;andReturn(0)</code></td></tr>
            <tr><td><code>Spy</code></td><td>enregistre les appels</td><td><code>Notification::fake()</code></td></tr>
            <tr><td><code>Mock</code></td><td>vérifie les attentes</td><td><code>$notifier-&gt;shouldReceive('send')-&gt;once()</code></td></tr>
            <tr><td><code>Fake</code></td><td>implémentation légère</td><td><code>Queue::fake()</code></td></tr>
          </tbody>
        </table>
      </div>

      <p class="footnote">Ne mockez pas ce que vous ne possédez pas. Préférez les fakes aux mocks. Un mock vérifie une chorégraphie; un fake vérifie un résultat.</p>`,
    },

    {
      section: 'Application',
      title: 'TDD dans Laravel',
      html: `${titleBlock(
        'APPLICATION',
        "Le test décrit le contrat, pas l'implémentation.",
        'Une requête, une réponse, un effet observable. Les détails restent dans les factories et les fakes.',
        true,
      )}

      <div class="split split-55-45">
        ${codeCard('tests/Feature/CourseCreationTest.php', `test('un professeur crée un cours', function () {
    Notification::fake();
    $professor = User::factory()->professor()->create();
    $student = User::factory()->student()->create();

    $payload = CoursePayload::for($professor)
        ->withStudents($student)
        ->toArray();

    $this->actingAs($professor)
        ->post(route('courses.store'), $payload)
        ->assertRedirect(route('dashboard'));

    expect(Course::count())->toBe(1);
    Notification::assertSentTo($student, CourseScheduleNotification::class);
});`, { className: 'code--sm' })}
        <div class="points">
          <div class="point"><span>01</span><p><b>Feature test</b>: une requête, une réponse, un effet.</p></div>
          <div class="point"><span>02</span><p><b>Factories</b>: des données valides, pas des détails.</p></div>
          <div class="point"><span>03</span><p><b>Fakes</b>: les effets de bord deviennent observables.</p></div>
        </div>
      </div>

      <p class="footnote">RefreshDatabase isole chaque test. Le test ne partage aucun état avec le précédent.</p>`,
    },

    {
      section: 'Application',
      title: 'TDD dans Livewire',
      html: `${titleBlock(
        'APPLICATION',
        "Testez le composant comme l'utilisateur.",
        "On ouvre, on saisit, on soumet. On observe l'état et les effets, pas les méthodes privées.",
        true,
      )}

      <div class="split split-55-45">
        ${codeCard('tests/Feature/CourseCreationTest.php', `Livewire::test('pages::calendar')
    ->call('openCreateModal')
    ->set('title', 'Algorithmique')
    ->set('room', 'Amphi B')
    ->set('dayOfWeek', Weekday::Monday->value)
    ->set('startTime', '08:00')
    ->set('endTime', '10:00')
    ->call('saveCourse')
    ->assertHasNoErrors()
    ->assertSet('showCourseModal', false);`, { className: 'code--sm' })}
        <div class="points">
          <div class="point"><span>01</span><p><b>Ouvrir</b>: on appelle l'action qui affiche la modale.</p></div>
          <div class="point"><span>02</span><p><b>Saisir</b>: on remplit les champs comme le formulaire.</p></div>
          <div class="point"><span>03</span><p><b>Observer</b>: erreurs, état, redirection, effets.</p></div>
        </div>
      </div>

      <p class="footnote">Le test Livewire remplace le clic, pas le navigateur. Pour le navigateur, utilisez Pest Browser.</p>`,
    },

    {
      section: 'Stratégie',
      title: 'Outside-in: la double boucle',
      html: `${titleBlock(
        'STRATÉGIE',
        'La boucle externe dit quoi.<br>La boucle interne dit comment.',
        "On commence par un test d'acceptance qui échoue, puis on descend dans les unités pour construire le comportement.",
        true,
      )}

      <div class="loop loop--lg">
        <div class="loop-step step-red">
          <span>01 · EXTERNE</span>
          <h3>Acceptance</h3>
          <p>Un test de feature décrit le parcours complet. Il échoue.</p>
        </div>
        <span class="loop-arrow" aria-hidden="true">→</span>
        <div class="loop-step step-green">
          <span>02 · INTERNE</span>
          <h3>Unités</h3>
          <p>Des tests rouges, verts, refactor sur le domaine et les collaborateurs.</p>
        </div>
        <span class="loop-arrow" aria-hidden="true">→</span>
        <div class="loop-step step-refactor">
          <span>03 · RETOUR</span>
          <h3>Acceptance</h3>
          <p>Le test externe passe. Le comportement est complet.</p>
        </div>
      </div>

      <blockquote class="pullquote">Le test d'acceptance vous empêche de construire une belle mécanique qui ne sert à rien.</blockquote>`,
    },

    {
      section: 'Stratégie',
      title: 'Refactorer sous les tests',
      html: `${titleBlock(
        'LE FILET',
        'Le refactor ne change pas le comportement.',
        'Les tests restent verts. Si vous devez les modifier, vous avez changé le comportement.',
        true,
      )}

      <div class="split split-50-50">
        <div class="points">
          <div class="point"><span>01</span><p><b>Un pas à la fois</b>: renommer, extraire, déplacer.</p></div>
          <div class="point"><span>02</span><p><b>Zéro test modifié</b>: le filet reste intact.</p></div>
          <div class="point"><span>03</span><p><b>Duplication éliminée</b>: le design émerge.</p></div>
        </div>
        ${codeCard('app/Domain/Slot.php', `public function overlaps(Slot $other): bool
{
    return $this->day === $other->day
        && $this->start < $other->end
        && $other->start < $this->end;
}`)}
      </div>

      <p class="footnote">Le refactor est la partie où le design émerge. Sans lui, TDD n'est qu'une technique de vérification.</p>`,
    },

    {
      section: 'Architecture',
      title: "arch() protège la forme du code",
      html: `${titleBlock(
        'GARDE-FOU',
        'Les tests protègent le comportement.<br>arch() protège la forme.',
        "Les tests d'architecture lisent le code et vérifient des règles collectives.",
        true,
      )}

      <div class="split split-55-45">
        ${codeCard('tests/Unit/ArchTest.php', `arch('models are persistence only')
    ->expect('App\\Models')
    ->not->toUse('Illuminate\\Http');

arch('enums stay framework agnostic')
    ->expect('App\\Enums')
    ->not->toUse('Illuminate');`)}
        <div class="failure">
          <span class="failure-label">MESSAGE D'ÉCHEC</span>
          <code class="failure-title">FAIL  models are persistence only</code>
          <code class="failure-msg">Expecting 'App\\Models' not to use 'Illuminate\\Http'.</code>
        </div>
      </div>

      <blockquote class="pullquote">Une règle qui n'est pas un test est une intention. Une règle qui est un test est une contrainte.</blockquote>`,
    },

    {
      layout: 'divider',
      chrome: false,
      title: 'Le programme',
      html: `<div class="divider">
        <span class="divider-numeral" aria-hidden="true">III</span>
        <div class="divider-copy">
          <p class="divider-kicker">PARTIE 03 · LE MODULE</p>
          <h2>Le programme</h2>
          <p class="divider-line">Dix sessions. Une fonctionnalité.</p>
        </div>
        <span class="divider-meta">Exercices · capstone · soutenance</span>
      </div>`,
    },

    {
      section: 'Le programme',
      title: 'Dix sessions, deux heures chacune',
      html: `${titleBlock(
        'LE PARCOURS',
        'Dix sessions, deux heures chacune.',
        'Chaque session: 20 minutes de théorie, 70 minutes de pratique, 20 minutes de débrief.',
        true,
      )}

      <div class="program-grid">
        <div class="program-item"><span class="program-num">01</span><div><h3>Pourquoi TDD</h3><p>Le coût du changement, la boucle, les trois lois.</p></div></div>
        <div class="program-item"><span class="program-num">02</span><div><h3>PHPUnit et Pest</h3><p>Assertions, expectations, anatomie d'un test.</p></div></div>
        <div class="program-item"><span class="program-num">03</span><div><h3>Baby steps</h3><p>Triangulation, fake it, liste de tests.</p></div></div>
        <div class="program-item"><span class="program-num">04</span><div><h3>Conception des tests</h3><p>AAA, fixtures, factories, test data builders.</p></div></div>
        <div class="program-item"><span class="program-num">05</span><div><h3>Doubles de test</h3><p>Stubs, spies, mocks, fakes, temps et hasard.</p></div></div>
        <div class="program-item"><span class="program-num">06</span><div><h3>TDD Laravel</h3><p>HTTP, base de données, validation, autorisation.</p></div></div>
        <div class="program-item"><span class="program-num">07</span><div><h3>TDD Livewire</h3><p>Composants, notifications, queues, événements.</p></div></div>
        <div class="program-item"><span class="program-num">08</span><div><h3>Outside-in</h3><p>Double boucle, walking skeleton, refactoring.</p></div></div>
        <div class="program-item"><span class="program-num">09</span><div><h3>Architecture et CI</h3><p>arch(), presets, suite rapide, mutation testing.</p></div></div>
        <div class="program-item"><span class="program-num">10</span><div><h3>Capstone</h3><p>Jours spéciaux, blocage et soutenance.</p></div></div>
      </div>

      <p class="footnote">Le fil rouge: une fonctionnalité de Campus Planning, du test rouge à la CI verte.</p>`,
    },

    {
      section: 'Le programme',
      title: 'On évalue le processus',
      html: `${titleBlock('ÉVALUATION', 'On évalue le processus, pas seulement le résultat.', 'Trois notes, un capstone, une participation.', true)}

      <div class="eval-grid">
        <article class="eval-card">
          <span class="eval-num">40%</span>
          <h3>Exercices</h3>
          <p>Chaque semaine: un test rouge, un vert, un refactor. Qualité des tests et régularité.</p>
        </article>
        <article class="eval-card">
          <span class="eval-num">50%</span>
          <h3>Capstone</h3>
          <p>Fonctionnalité, tests, journal TDD, règles d'architecture, CI verte.</p>
        </article>
        <article class="eval-card">
          <span class="eval-num">10%</span>
          <h3>Participation</h3>
          <p>Quiz, revues de code, débriefs. La clarté du raisonnement compte.</p>
        </article>
      </div>

      <p class="footnote">Un capstone qui marche sans tests échoue. Un capstone avec des tests fragiles plafonne.</p>`,
    },

    {
      section: 'Le programme',
      title: 'Gérer les jours spéciaux, test-first',
      html: `${titleBlock('CAPSTONE', 'Gérer les jours spéciaux, test-first.', 'Un admin ajoute une date. Les professeurs ne peuvent plus programmer de cours ce jour-là.', true)}

      <div class="split split-52-48">
        <div class="rules">
          <div class="rule"><span class="check"></span><p>Un admin ajoute un jour spécial.</p></div>
          <div class="rule"><span class="check"></span><p>Un professeur ne peut pas y programmer un cours.</p></div>
          <div class="rule"><span class="check"></span><p>Un professeur ne peut pas y déplacer un cours.</p></div>
          <div class="rule"><span class="check"></span><p>Le jour spécial est visible dans le calendrier.</p></div>
          <div class="rule"><span class="check"></span><p>La semaine suivante n'est pas bloquée.</p></div>
          <div class="rule"><span class="check"></span><p>Un étudiant ne peut pas gérer les jours spéciaux.</p></div>
          <div class="deliverables" style="margin-top: 16px">
            <span>Tests</span>
            <span>Migration</span>
            <span>Admin page</span>
            <span>Journal TDD</span>
            <span>CI verte</span>
          </div>
        </div>

        <div class="capstone-side">
          ${codeCard('tests/Feature/SpecialDayCourseTest.php', `test('un professeur ne peut pas programmer un jour spécial', function () {
    $admin = User::factory()->admin()->create();
    $professor = User::factory()->professor()->create();

    SpecialDay::factory()->create([
        'date' => '2026-11-11',
        'created_by' => $admin->id,
    ]);

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->set('title', 'Algorithmique avancée')
        ->set('room', 'Amphi B')
        ->set('dayOfWeek', Weekday::Wednesday->value)
        ->set('startTime', '08:00')
        ->set('endTime', '10:00')
        ->call('saveCourse')
        ->assertHasErrors(['dayOfWeek']);
});`, { className: 'code--sm' })}
        </div>
      </div>`,
    },

    {
      section: 'Le programme',
      title: 'Six façons de rater son TDD',
      html: `${titleBlock('PIÈGES', 'Six façons de rater son TDD.', 'Six pièges qui reviennent à chaque promotion.', true)}

      <div class="family-grid">
        <article class="family">
          <span class="family-index">01</span>
          <h3>Coder d'abord</h3>
          <p class="fix">Le test devient une justification.</p>
          <p>Écrire le test avant la première ligne de code.</p>
        </article>
        <article class="family">
          <span class="family-index">02</span>
          <h3>Tester l'implémentation</h3>
          <p class="fix">Le test casse au moindre refactor.</p>
          <p>Tester le comportement observable.</p>
        </article>
        <article class="family">
          <span class="family-index">03</span>
          <h3>Un test géant</h3>
          <p class="fix">On ne sait plus ce qui a échoué.</p>
          <p>Un comportement par test.</p>
        </article>
        <article class="family">
          <span class="family-index">04</span>
          <h3>Tout mocker</h3>
          <p class="fix">Le test vérifie la chorégraphie.</p>
          <p>Préférer les fakes et les vrais objets.</p>
        </article>
        <article class="family">
          <span class="family-index">05</span>
          <h3>Viser 100%</h3>
          <p class="fix">La couverture devient un chiffre.</p>
          <p>Couvrir les comportements, pas les lignes.</p>
        </article>
        <article class="family">
          <span class="family-index">06</span>
          <h3>Sauter le refactor</h3>
          <p class="fix">Le code passe, le design pourrit.</p>
          <p>Refactorer à chaque cycle vert.</p>
        </article>
      </div>

      <p class="footnote">Le TDD n'est pas une course au vert. C'est une discipline de petits pas.</p>`,
    },

    {
      layout: 'closing',
      chrome: false,
      title: "Le test est une description du comportement",
      html: `<div class="closing">
        <p class="kicker"><span></span>À RETENIR</p>
        <h2>Le test n'est pas une preuve.<br>C'est une <em>description</em> du comportement.</h2>
        <p class="closing-lede">20 heures pour changer la façon dont vous écrivez du code. Pas pour ajouter des tests à la fin.</p>
        <div class="closing-foot">
          <span class="closing-path">docs/tdd/module-20h/</span>
          <span class="closing-command">php artisan test</span>
        </div>
      </div>`,
    },
  ];

  window.SLIDES = slides;
})();
