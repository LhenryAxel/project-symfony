<?php

namespace App\Command;

use App\Repository\ImageRepository;
use App\Repository\StatRepository;
use App\Repository\TypeStatRepository;
use App\Enum\TypeStat;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lc:excel',
    description: 'Génère un fichier Excel avec les stats des images.',
)]
class LcExcelCommand extends Command
{
    public function __construct(
        private ImageRepository $imageRepo,
        private StatRepository $statRepo,
        private TypeStatRepository $typeStatRepo
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('out', null, InputOption::VALUE_REQUIRED, 'Chemin du fichier Excel de sortie', 'images_stats.xlsx');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getOption('out');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stats Images');

        // Entêtes enrichies
        $sheet->fromArray([
            'ID',
            'Nom de fichier',
            'Date d\'upload',
            'Vues',
            'Requêtes',
            'Téléchargements'
        ], null, 'A1');

        $typeVue = $this->typeStatRepo->find(TypeStat::Vue->value);
        $typeRequest = $this->typeStatRepo->find(TypeStat::RequeteUrl->value);
        $typeDownload = $this->typeStatRepo->find(TypeStat::Telechargement->value);

        $images = $this->imageRepo->findAll();
        $row = 2;

        foreach ($images as $image) {
            $viewCount = count($this->statRepo->findByImageAndType($image, $typeVue));
            $requestCount = count($this->statRepo->findByImageAndType($image, $typeRequest));
            $downloadCount = count($this->statRepo->findByImageAndType($image, $typeDownload));

            $sheet->fromArray([
                $image->getId(),
                $image->getFilename(),
                $image->getUploadedAt()?->format('Y-m-d H:i:s') ?? 'N/A',
                $viewCount,
                $requestCount,
                $downloadCount
            ], null, 'A' . $row);

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        $output->writeln("<info>Fichier généré : $filename</info>");

        return Command::SUCCESS;
    }
}
