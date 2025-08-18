<nav class="main-nav" role="navigation">

    <!-- Mobile menu toggle button (hamburger/x icon) -->
    <input id="main-menu-state" type="checkbox" />
    <label class="main-menu-btn" for="main-menu-state">
        <span class="main-menu-btn-icon"></span> Toggle main menu visibility
    </label>

    <ul id="main-menu" class="sm sm-blue">

        <!-- Tableau de bord -->
        @can('voir-dashboard')
        <li class="@active('home')">
            <a href="{{ route('home') }}"><i class="icon-Home"></i>Tableau de bord</a>
        </li>
        @endcan

        <!-- Ventes -->
        @can('voir-ventes')
        <li class="@active('sells')">
            <a href="{{ route('sells') }}">
                <i class="icon-Dollar"></i> Ventes
            </a>
        </li>
        @endcan

        <!-- Commandes -->
        @can('voir-commandes')
        <li class="@active('orders')">
            <a href="{{ route('orders') }}">
                <i class="icon-Dinner1"></i>Commandes
                <span class="label label-danger">5</span>
            </a>
        </li>
        @endcan

        <!-- Utilisateurs -->
        @can('manage-users')
        <li class="@active(['users.*', 'users'])">
            <a href="#"><i class="icon-Add-user"></i>Utilisateurs</a>
            <ul>
                @can('voir-utilisateurs')
                <li class="@active('users')">
                    <a href="{{ route('users') }}"><i class="icon-Commit"></i>Comptes Utilisateurs</a>
                </li>
                @endcan
            </ul>
        </li>
        @endcan

        <!-- Factures -->
        @can('voir-factures')
        <li class="@active('factures')">
            <a href="{{ route('factures') }}">
                <i class="icon-Selected-file"></i>
                Factures <span class="label label-success ms-1">2</span>
            </a>
        </li>
        @endcan

        <!-- Gestion serveurs -->
        @can('voir-serveurs')
        <li class="@active(['serveurs.*'])">
            <a href="#"><i class="icon-Group"></i>Gestion serveurs</a>
            <ul>
                @can('voir-serveurs')
                <li class="@active('serveurs')">
                    <a href="{{ route('serveurs') }}">Liste des serveurs</a>
                </li>
                @endcan
                @can('voir-activites-serveurs')
                <li class="@active('serveurs.activities')">
                    <a href="{{ route('serveurs.activities') }}">Serveurs en service <span class="label label-warning ms-1">5</span></a>
                </li>
                @endcan
                @can('voir-produits-vendus')
                <li>
                    <a href="{{ route('sells') }}">Produits vendus <span class="label label-success ms-1">5</span></a>
                </li>
                @endcan
            </ul>
        </li>
        @endcan

        <!-- Produits & Stock -->
        @can('voir-produits')
        <li class="@active(['products.*','products'])">
            <a href="#"><i class="icon-Cart"></i>Produits</a>
            <ul>
                @can('voir-categories')
                <li class="@active('products.categories')">
                    <a href="{{ route('products.categories') }}">Catégories</a>
                </li>
                @endcan
                @can('voir-produits')
                <li class="@active('products')">
                    <a href="{{ route('products') }}">Produits</a>
                </li>
                @endcan
                @can('voir-mouvements-stock')
                <li class="@active('products.mvts')">
                    <a href="{{ route('products.mvts') }}">Mouvements stock</a>
                </li>
                @endcan
            </ul>
        </li>
        @endcan

        <!-- Emplacements & Tables -->
        @can('voir-tables')
        <li class="@active(['tables.*'])">
            <a href="#"><i class="icon-Layout-grid"></i>Emplacements</a>
            <ul>
                @can('voir-occupations-tables')
                <li class="@active('tables.occuped')">
                    <a href="{{ route('tables.occuped') }}">Occupations des tables <span class="label label-danger ms-1">2</span></a>
                </li>
                @endcan
                @can('voir-emplacements')
                <li class="@active('tables.emplacements')">
                    <a href="{{ route('tables.emplacements') }}">Emplacements</a>
                </li>
                @endcan
                @can('voir-tables')
                <li class="@active('tables')">
                    <a href="{{ route('tables') }}">Tables</a>
                </li>
                @endcan
                @can('voir-chambres')
                <li class="#">
                    <a href="#">Chambres</a>
                </li>
                @endcan
            </ul>
        </li>
        @endcan

        <!-- Rapports -->
        @can('voir-rapports')
        <li class="@active(['reports.*'])">
            <a href="#"><i class="icon-Equalizer"></i>Rapports</a>
            <ul>
                <li class="@active('reports.global')">
                    <a href="{{ route('reports.global') }}">Rapports journaliers des ventes</a>
                </li>
            </ul>
        </li>
        @endcan

    </ul>
</nav>
