# Arkivdatabasen til WordPress

Pluginet udvikles af [Maltex Gruppen](https://www.maltex.dk/).
Søgning og visning af data fra [Arkivdatabasen](https://www.arkivdatabasen.dk/) via shortcoden `[adb]`.
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

Plugin Update Checker 5.6 er medleveret i `vendor/plugin-update-checker` (MIT-licens), upstream commit `a2db6871deec989a74e1f90fafc6d58ae526a879`. Det kræver ingen Composer-installation. Pluginet er GPL-2.0-or-later; se LICENSE. Sorttable har sin oprindelige licens i filheaderen.
