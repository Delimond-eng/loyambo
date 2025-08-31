@if (!Route::is("licences.pricing"))
    <nav class="main-nav" role="navigation">
    <!-- Mobile menu toggle button (hamburger/x icon) -->
    <input id="main-menu-state" type="checkbox" />
    <label class="main-menu-btn" for="main-menu-state">
        <span class="main-menu-btn-icon"></span> Toggle main menu visibility
    </label>

    <ul id="main-menu" class="sm sm-blue">

        <!-- Menu Tdb -->
        @can('voir-dashboard')
        <li class="@active('home')">
            <a href="{{ route('home') }}"><i class="icon-Home"></i>Tableau de bord</a>
        </li>
        @endcan
        <!-- End Tbd -->

        <!-- Menu Ventes -->
        @can('voir-ventes')
        <li class="@active('sells')">
            <a href="{{ route('sells') }}">
                <i class="icon-Dollar"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                Ventes
            </a>
        </li>
        @endcan
        <!-- End Ventes -->

        <!-- Menu Commandes -->
        @can('voir-commandes')
        <li class="@active('orders')">
            <a href="{{ route('orders') }}">
                <i class="icon-Dinner1"><span class="path1"></span><span class="path2"></span></i>
                Commandes
                <span class="label label-danger">5</span>
            </a>
        </li>
        @endcan
        <!-- End Commandes -->

        <!-- Menu users -->
        @can("manage-users")
        <li class="@active(['users.*', 'users'])">
            <a href="#">
                <i class="icon-Add-user"><span class="path1"></span><span class="path2"></span></i>Utilisateurs
            </a>
            <ul>
                @can('voir-utilisateurs')
                <li class="@active('users')">
                    <a href="{{ route('users') }}">
                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                        Comptes Utilisateurs
                    </a>
                </li>
                @endcan
                <!-- @can('gerer-roles') {{-- ou permission personnalisée si nécessaire --}}
                <li>
                    <a href="#">
                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                        Rôles & attribution accès
                    </a>
                </li>
                @endcan -->
            </ul>
        </li>
        @endcan
        <!-- End users -->

        <!-- Menu factures -->
        @can('voir-factures')
        <li class="@active('factures')">
            <a href="{{ route('factures') }}">
                <i class="icon-Selected-file"><span class="path1"></span><span class="path2"></span></i>
                Factures <span class="label label-success ms-1">2</span>
            </a>
        </li>
        @endcan
        <!-- end Factures -->

        <!-- Menu serveurs -->
        @can('voir-serveurs')
        <li class="@active(['serveurs.*'])">
            <a href="#">
                <i class="icon-Group"><span class="path1"></span><span class="path2"></span></i>Gestion serveurs
            </a>
            <ul>
                @can('voir-serveurs')
                <li class="@active('serveurs')">
                    <a href="{{ route('serveurs') }}">
                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                        Liste des serveurs
                    </a>
                </li>
                @endcan
                @can('voir-activites-serveurs')
                <li class="@active('serveurs.activities')">
                    <a href="{{ route('serveurs.activities') }}">
                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                        Serveurs en service <span class="label label-warning ms-1">5</span>
                    </a>
                </li>
                @endcan
                @can('voir-produits-vendus')
                <li>
                    <a href="{{ route('sells') }}">
                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                        Produits vendus <span class="label label-success ms-1">5</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcan
        <!-- End Serveurs -->

        <!-- Menu produits & stock -->
        @can('voir-produits')
        <li class="@active(['products.*','products'])">
            <a href="#">
                <i class="icon-Cart"><span class="path1"></span><span class="path2"></span></i>Produits
            </a>
            <ul>
                @can('voir-categories')
                <li class="@active('products.categories')">
                    <a href="{{ route('products.categories') }}">
                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                        Catégories
                    </a>
                </li>
                @endcan
                @can('voir-produits')
                <li class="@active('products')">
                    <a href="{{ route('products') }}">
                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                        Produits
                    </a>
                </li>
                @endcan
                @can('voir-mouvements-stock')
                <li class="@active('products.mvts')">
                    <a href="{{ route('products.mvts') }}">
                        <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
                        Mouvements stock
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcan
        <!-- end Produits -->

        <!-- Menu emplacements & Tables -->
        <li class="@active(['tables.*', 'beds.occuped']) AppPlace">
            <a href="#">
                <i class="icon-Layout-grid"><span class="path1"></span><span class="path2"></span></i>
                Emplacements
            </a>
            <ul>
                @can('voir-occupations-tables')
                <li class="@active('tables.occuped')">
                    <a href="{{ route('tables.occuped') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Occupations des tables <span class="label label-danger ms-1">@{{ tablePendingsCount }}</span></a>
                </li>
                @endcan
                @can('voir-chambres')
                <li class="@active('beds.occuped')">
                    <a href="{{ route('beds.occuped') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Occupations des Chambres <span class="label label-danger ms-1">@{{ bedPendingsCount }}</span></a>
                </li>
                @endcan
                @can('voir-emplacements')
                <li class="@active('tables.emplacements')">
                    <a href="{{ route('tables.emplacements') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Emplacements</a>
                </li>
                @endcan
                @can('voir-tables')
                <li class="@active('tables')">
                    <a href="{{ route('tables') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Tables & chambres</a>
                </li>
                @endcan
            </ul>
        </li>
        <!-- end emplacements -->

        <!-- Menu rapports -->
        @can('voir-rapports')
        <li class="@active(['reports.*'])">
            <a href="#">
                <i class="icon-Equalizer"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                Rapports
            </a>
            <ul>
                <li class="@active('reports.global')">
                    <a href="{{ route('reports.global') }}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Rapports journaliers des ventes</a>
                </li>
            </ul>
        </li>
        @endcan
        <!-- end rapports -->
    </ul>
</nav>
@endif


@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/places.js") }}"></script>	
@endpush
