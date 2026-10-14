# Arkivdatabasen til WordPress

Søgning og visning af data fra Arkivdatabasens API via shortcoden `[adb]`.
Kræver WordPress 6.0 eller nyere og PHP 7.4 eller nyere.

## Installation og automatisk opdatering

1. Hent **arkivdatabasen.zip** fra den seneste stabile [GitHub Release](https://github.com/MaltexDK/wp-arkivdatabasen/releases/latest). Brug releasefilen, ikke GitHubs “Source code”-ZIP.
2. Upload under **Plugins → Tilføj nyt plugin → Upload plugin**, og aktivér.
3. Angiv URL og API-nøgle under **Indstillinger → Arkivdatabasen**. Eksisterende `adb_settings` bevares ved opdatering.
4. Indsæt `[adb]` på en side.
5. Vælg **Aktivér autoopdateringer** for Arkivdatabasen på pluginsiden. WordPress' planlagte opgaver skal køre, og installationen skal kunne skrive pluginfiler.

Version 1.2 har ingen updater og skal først opgraderes manuelt til 1.3.0.
Derefter kontrolleres stabile GitHub Releases regelmæssigt. Opdatering aktiveres af administratoren, aldrig tvangsmæssigt af pluginet. Drafts, prereleases og udgivelser uden den korrekte release-ZIP tilbydes ikke.
WordPress-serveren skal have HTTPS-adgang til api.github.com, github.com og GitHubs download-/redirectværter, herunder release-assets.githubusercontent.com. Intet GitHub-token kræves for offentlige downloads. GitHubs anonyme API-rategrænser gælder.

## Repositoryindstillinger på GitHub

- **Settings → General → Danger Zone → Change repository visibility → Public**. Gennemgå først historikken for private data; offentliggørelse omfatter også gamle commits.
- **Settings → Actions → General**: tillad GitHub Actions samt de anvendte actions. Organisationens politik må ikke blokere workflowets `contents: write`.
- Ingen GitHub Pages, ekstra opdateringsserver eller personlige tokens er nødvendige.
- Beskyt `main` efter behov og tillad kun betroede vedligeholdere at oprette release-tags.

## Udgiv en ny version

1. Ret både `Version` og `ADB_VERSION` i `arkivdatabasen.php`, og opdatér CHANGELOG.md.
2. Kør regressionstesten mod en lokal WordPress, kontrollér PHP-syntaks, og byg med `python3 scripts/build-release.py v1.3.0` (brug det nye versionsnummer).
3. Commit og push ændringerne. Opret og push tagget, fx `git tag v1.3.0` og `git push origin v1.3.0`.
4. Workflowet `.github/workflows/release.yml` bygger `arkivdatabasen.zip` og opretter en stabil Release med filen. Kontroller workflowresultatet og download ZIP-filen anonymt.

Tagget skal matche pluginheaderen. Flyt ikke allerede udgivne tags. Hvis workflowet afvises, kan den lokalt byggede ZIP uploades til en manuelt oprettet Release med samme tag.

## Udvikling og tests

`tests/regression.php` køres med PHP i en installeret WordPress, hvor pluginet er aktivt:

```sh
WP_LOAD_PATH=/sti/til/wordpress/wp-load.php php tests/regression.php
npm ci --ignore-scripts
npm test
node --check includes/adb-ui.js
python3 scripts/build-release.py v1.3.0
```

Testen bruger lokale syntetiske HTTP-svar og ændrer ikke arkivindstillingerne. Den validerer søgning, detaljevisning, encoding, escaping, fejlsvar og GitHub-opdateringsmetadata. Den erstatter ikke en afprøvning mod det virkelige arkiv.

API-signaturen er uændret: MD5 af API-nøgle + UTC-timestamp. Gyldig UTF-8 JSON bruges direkte; ISO-8859-1 understøttes som fallback ved ugyldig UTF-8. Billedvalget `featherlight` fra ældre indstillinger bruger nu en lokal, indbygget browserdialog. Andre billedplugins kan fortsat bruges med valget “ekstern”.

Plugin Update Checker 5.6 er medleveret i `vendor/plugin-update-checker` (MIT-licens), upstream commit `a2db6871deec989a74e1f90fafc6d58ae526a879`. Det kræver ingen Composer-installation. Pluginet er GPL-2.0-or-later; se LICENSE. Sorttable har sin oprindelige licens i filheaderen.
