<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Fournisseur;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-tunisian-data',
    description: 'Charge des exemples de données tunisiennes (catégories, fournisseurs, produits en DT)',
)]

class LoadTunisianDataCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // ─── Catégories tunisiennes ───
        $categoriesData = [
            ['nom' => 'Épicerie & Conserves', 'description' => 'Harissa, tomates séchées, huile d\'olive tunisienne, conserves, épices et condiments traditionnels.'],
            ['nom' => 'Pâtisserie & Biscuits', 'description' => 'Makroudh, baklawa, kaak warka, biscuits tunisiens, ghribia et douceurs traditionnelles.'],
            ['nom' => 'Boissons', 'description' => 'Boga, Safia, Ain Garci, jus Délice, thé, café turc et boissons locales.'],
            ['nom' => 'Produits Laitiers', 'description' => 'Lait Délice, yaourt Vitalait, fromage Président Tunisie, leben et rayeb.'],
            ['nom' => 'Céréales & Légumineuses', 'description' => 'Couscous, frik, lentilles, pois chiches, fèves et blé tunisien.'],
            ['nom' => 'Huiles & Olives', 'description' => 'Huile d\'olive extra vierge de Sfax, olives de table, huile de Sahel et CHO.'],
            ['nom' => 'Cosmétiques & Hygiène', 'description' => 'Produits El Baraka, savon de Kélibia, henné, cosmétiques naturels tunisiens.'],
            ['nom' => 'Électronique & Accessoires', 'description' => 'Téléphones, tablettes, accessoires électroniques, câbles et chargeurs.'],
        ];

        $categories = [];
        foreach ($categoriesData as $catData) {
            $cat = new Category();
            $cat->setNom($catData['nom']);
            $cat->setDescription($catData['description']);
            $this->em->persist($cat);
            $categories[$catData['nom']] = $cat;
        }

        $io->info('8 catégories créées.');

        // ─── Fournisseurs tunisiens ───
        $fournisseursData = [
            ['nom' => 'Société SICAM', 'email' => 'contact@sicam.com.tn', 'telephone' => '+216 71 940 500', 'adresse' => 'Zone Industrielle Ben Arous, Tunis'],
            ['nom' => 'Groupe Délice Holding', 'email' => 'info@delice.tn', 'telephone' => '+216 71 862 000', 'adresse' => 'Route de Tunis Km 3, Ben Arous 2013'],
            ['nom' => 'Société El Mazraa', 'email' => 'commercial@elmazraa.tn', 'telephone' => '+216 71 601 300', 'adresse' => 'Zone Industrielle Mégrine, Ben Arous'],
            ['nom' => 'CHO Company (Huile Terra Delyssa)', 'email' => 'export@cho.com.tn', 'telephone' => '+216 74 431 400', 'adresse' => 'Route de Gabès Km 0.5, Sfax 3003'],
            ['nom' => 'Poulina Group Holding', 'email' => 'info@pfrgroup.com.tn', 'telephone' => '+216 71 805 300', 'adresse' => 'Les Berges du Lac 2, Tunis 1053'],
            ['nom' => 'SOTUBI (Boga)', 'email' => 'contact@sotubi.com.tn', 'telephone' => '+216 71 429 500', 'adresse' => 'Avenue Habib Bourguiba, Tunis'],
            ['nom' => 'Société Vitalait', 'email' => 'info@vitalait.com.tn', 'telephone' => '+216 77 410 200', 'adresse' => 'Zone Industrielle Sidi Bouzid'],
            ['nom' => 'Hamdi Frères', 'email' => 'contact@hamdi.com.tn', 'telephone' => '+216 72 286 100', 'adresse' => 'Nabeul, Cap Bon, Tunisie'],
        ];

        $fournisseurs = [];
        foreach ($fournisseursData as $fData) {
            $f = new Fournisseur();
            $f->setNom($fData['nom']);
            $f->setEmail($fData['email']);
            $f->setTelephone($fData['telephone']);
            $f->setAdresse($fData['adresse']);
            $this->em->persist($f);
            $fournisseurs[$fData['nom']] = $f;
        }

        $io->info('8 fournisseurs créés.');

        // ─── Produits tunisiens (prix en Dinars Tunisiens) ───
        $productsData = [
            // Épicerie & Conserves
            ['libelle' => 'Harissa SICAM 380g', 'description' => 'Harissa traditionnelle tunisienne au piment rouge et épices, tube 380g.', 'price' => 2.850, 'stock' => 150, 'category' => 'Épicerie & Conserves', 'fournisseur' => 'Société SICAM'],
            ['libelle' => 'Double concentré de tomate SICAM 800g', 'description' => 'Concentré de tomate double, boîte 800g, qualité premium.', 'price' => 3.200, 'stock' => 120, 'category' => 'Épicerie & Conserves', 'fournisseur' => 'Société SICAM'],
            ['libelle' => 'Thon El Mazraa 160g', 'description' => 'Thon à l\'huile d\'olive, boîte 160g, pêche Méditerranée.', 'price' => 4.750, 'stock' => 200, 'category' => 'Épicerie & Conserves', 'fournisseur' => 'Société El Mazraa'],
            ['libelle' => 'Tabel moulu 50g', 'description' => 'Mélange d\'épices tunisiennes (coriandre, carvi), sachet 50g.', 'price' => 1.200, 'stock' => 80, 'category' => 'Épicerie & Conserves', 'fournisseur' => 'Hamdi Frères'],
            
            // Pâtisserie & Biscuits
            ['libelle' => 'Makroudh aux dattes 500g', 'description' => 'Makroudh traditionnel de Kairouan farci aux dattes Deglet Nour.', 'price' => 8.500, 'stock' => 60, 'category' => 'Pâtisserie & Biscuits', 'fournisseur' => 'Hamdi Frères'],
            ['libelle' => 'Baklawa pistache assortie 1kg', 'description' => 'Assortiment de baklawa tunisienne aux pistaches et amandes.', 'price' => 25.000, 'stock' => 30, 'category' => 'Pâtisserie & Biscuits', 'fournisseur' => 'Hamdi Frères'],
            ['libelle' => 'Kaak Warka paquet 200g', 'description' => 'Biscuits secs tunisiens au sésame, paquet de 200g.', 'price' => 3.800, 'stock' => 90, 'category' => 'Pâtisserie & Biscuits', 'fournisseur' => 'Poulina Group Holding'],
            ['libelle' => 'Ghribia aux amandes 400g', 'description' => 'Ghribia traditionnelle aux amandes et sucre glace, boîte 400g.', 'price' => 12.000, 'stock' => 5, 'category' => 'Pâtisserie & Biscuits', 'fournisseur' => 'Hamdi Frères'],

            // Boissons
            ['libelle' => 'Boga Cidre pack 6x25cl', 'description' => 'Pack de 6 bouteilles Boga Cidre gazéifié, 25cl chacune.', 'price' => 5.400, 'stock' => 180, 'category' => 'Boissons', 'fournisseur' => 'SOTUBI (Boga)'],
            ['libelle' => 'Eau minérale Safia 1.5L x6', 'description' => 'Pack de 6 bouteilles d\'eau minérale Safia, 1.5L.', 'price' => 3.600, 'stock' => 250, 'category' => 'Boissons', 'fournisseur' => 'SOTUBI (Boga)'],
            ['libelle' => 'Jus Délice Orange 1L', 'description' => 'Jus d\'orange 100% pur jus, brique 1L Délice.', 'price' => 3.950, 'stock' => 100, 'category' => 'Boissons', 'fournisseur' => 'Groupe Délice Holding'],
            ['libelle' => 'Thé vert à la menthe 200g', 'description' => 'Thé vert de Chine parfumé à la menthe, boîte 200g.', 'price' => 6.800, 'stock' => 3, 'category' => 'Boissons', 'fournisseur' => 'Hamdi Frères'],

            // Produits Laitiers
            ['libelle' => 'Lait Délice demi-écrémé 1L', 'description' => 'Lait UHT demi-écrémé Délice, brique 1L.', 'price' => 1.480, 'stock' => 300, 'category' => 'Produits Laitiers', 'fournisseur' => 'Groupe Délice Holding'],
            ['libelle' => 'Yaourt Vitalait Nature x4', 'description' => 'Pack de 4 yaourts nature brassés Vitalait, 110g chacun.', 'price' => 3.200, 'stock' => 120, 'category' => 'Produits Laitiers', 'fournisseur' => 'Société Vitalait'],
            ['libelle' => 'Fromage frais Délice 200g', 'description' => 'Fromage frais à tartiner Délice, barquette 200g.', 'price' => 2.950, 'stock' => 80, 'category' => 'Produits Laitiers', 'fournisseur' => 'Groupe Délice Holding'],
            ['libelle' => 'Leben traditionnel 500ml', 'description' => 'Lait fermenté traditionnel tunisien, bouteille 500ml.', 'price' => 1.800, 'stock' => 0, 'category' => 'Produits Laitiers', 'fournisseur' => 'Société Vitalait'],

            // Céréales & Légumineuses
            ['libelle' => 'Couscous fin 1kg', 'description' => 'Couscous de blé dur fin, qualité supérieure, paquet 1kg.', 'price' => 2.400, 'stock' => 200, 'category' => 'Céréales & Légumineuses', 'fournisseur' => 'Poulina Group Holding'],
            ['libelle' => 'Pois chiches secs 500g', 'description' => 'Pois chiches secs calibrés, origine Tunisie, sachet 500g.', 'price' => 2.100, 'stock' => 150, 'category' => 'Céréales & Légumineuses', 'fournisseur' => 'Poulina Group Holding'],
            ['libelle' => 'Frik (blé vert concassé) 500g', 'description' => 'Frik traditionnel, blé vert concassé pour chorba, sachet 500g.', 'price' => 4.500, 'stock' => 70, 'category' => 'Céréales & Légumineuses', 'fournisseur' => 'Hamdi Frères'],
            ['libelle' => 'Lentilles corail 400g', 'description' => 'Lentilles corail pour soupe et purée, sachet 400g.', 'price' => 3.300, 'stock' => 2, 'category' => 'Céréales & Légumineuses', 'fournisseur' => 'Poulina Group Holding'],

            // Huiles & Olives
            ['libelle' => 'Huile d\'olive extra vierge Terra Delyssa 750ml', 'description' => 'Huile d\'olive extra vierge premium, première pression à froid, Sfax.', 'price' => 18.500, 'stock' => 90, 'category' => 'Huiles & Olives', 'fournisseur' => 'CHO Company (Huile Terra Delyssa)'],
            ['libelle' => 'Olives noires de Tebourba 400g', 'description' => 'Olives noires naturelles de Tebourba, bocal 400g.', 'price' => 5.200, 'stock' => 60, 'category' => 'Huiles & Olives', 'fournisseur' => 'CHO Company (Huile Terra Delyssa)'],
            ['libelle' => 'Huile d\'olive Ruspina 1L', 'description' => 'Huile d\'olive vierge Ruspina, bouteille 1L, origine Sahel.', 'price' => 15.900, 'stock' => 40, 'category' => 'Huiles & Olives', 'fournisseur' => 'CHO Company (Huile Terra Delyssa)'],

            // Cosmétiques & Hygiène
            ['libelle' => 'Savon de Kélibia à l\'huile d\'olive 100g', 'description' => 'Savon artisanal de Kélibia, 100% huile d\'olive, 100g.', 'price' => 4.200, 'stock' => 45, 'category' => 'Cosmétiques & Hygiène', 'fournisseur' => 'Hamdi Frères'],
            ['libelle' => 'Henné naturel du Kef 250g', 'description' => 'Henné naturel en poudre, origine Le Kef, sachet 250g.', 'price' => 7.500, 'stock' => 35, 'category' => 'Cosmétiques & Hygiène', 'fournisseur' => 'Hamdi Frères'],
            ['libelle' => 'Eau de rose El Baraka 200ml', 'description' => 'Eau de rose naturelle pour visage et pâtisserie, flacon 200ml.', 'price' => 5.800, 'stock' => 0, 'category' => 'Cosmétiques & Hygiène', 'fournisseur' => 'Hamdi Frères'],

            // Électronique & Accessoires
            ['libelle' => 'Câble USB-C charge rapide 1m', 'description' => 'Câble USB-C vers USB-A, charge rapide 3A, longueur 1m.', 'price' => 9.900, 'stock' => 100, 'category' => 'Électronique & Accessoires', 'fournisseur' => 'Poulina Group Holding'],
            ['libelle' => 'Écouteurs Bluetooth sans fil', 'description' => 'Écouteurs Bluetooth 5.0 avec boîtier de charge, autonomie 4h.', 'price' => 45.000, 'stock' => 25, 'category' => 'Électronique & Accessoires', 'fournisseur' => 'Poulina Group Holding'],
            ['libelle' => 'Coque silicone iPhone 15', 'description' => 'Coque de protection en silicone souple pour iPhone 15.', 'price' => 12.500, 'stock' => 4, 'category' => 'Électronique & Accessoires', 'fournisseur' => 'Poulina Group Holding'],
        ];

        foreach ($productsData as $pData) {
            $product = new Product();
            $product->setLibelle($pData['libelle']);
            $product->setDescription($pData['description']);
            $product->setPrice($pData['price']);
            $product->setStock($pData['stock']);
            $product->setCategory($categories[$pData['category']]);
            $product->setFournisseur($fournisseurs[$pData['fournisseur']]);
            $this->em->persist($product);
        }

        $io->info(count($productsData) . ' produits créés.');

        $this->em->flush();

        $io->success('Données tunisiennes chargées avec succès ! (' . count($categoriesData) . ' catégories, ' . count($fournisseursData) . ' fournisseurs, ' . count($productsData) . ' produits)');

        return Command::SUCCESS;
    }
}
