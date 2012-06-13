<?

class ParallelCommandProcessor extends SingleCommandProcessor
{
  public function process()
  {
    $start = microtime(true);
    $makefile = '';
    $job = 0;
    $jobs = array();
    foreach($this->commands as $cmd)
    {
      $job_name = "job{$job}";
      $jobs[] = $job_name;
      $makefile .= "{$job_name}:\n\t$cmd\n\n";
      $job++;
    }
    $jobs = join(' ', $jobs);
    $makefile = "all: $jobs\n\n".$makefile;
    file_put_contents("tmp/makefile", $makefile);
    
    cmd("make -j 10 -f tmp/makefile");
    $end = microtime(true);
    dprint(sprintf("Processed %d commands in %f seconds.", count($this->commands), $end-$start), false);
  }

}