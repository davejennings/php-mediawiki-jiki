<?php
if(!defined("MEDIAWIKI"))
{
   echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
   die(1);
}
/**
 * a simple no frills renderer for JIRA data
 */
class Simple
{
  /**
   * get the rendered view in string format
   *
   * @param array data the array of data to be rendered
   */
  function getRenderedView(&$data,$args=array())
  {
    $renderedView = "";
    foreach($data["data"] as $issue)
    {
      $renderedView.= "{$issue["key"]} {$issue["fields"]["summary"]} [ {$issue["fields"]["issuetype"]["name"]} ]( {$issue["fields"]["status"]["name"]} ) ";
      if(isset($issue["fixVersions"]))
      {
        foreach($issue["fixVersions"] as $issueFixVersion)
        {
          $renderedView.= "(version {$issueFixVersion["name"]}";
          if(isset($issueFixVersion["releaseDate"])&&isset($issueFixVersion["released"])&&$issueFixVersion["released"]===true)
          {
            $renderedView.= " - {$issueFixVersion["releaseDate"]}";
          }
          $renderedView.= " )";
        }
      }
      if(isset($args["renderDetails"])&&$args["renderDetails"]===true)
      {
        $renderedView.= "<br/>  {$issue["fields"][JIKI_FULL_DETAILS_FIELD]}";
      }
      $renderedView.= "<br/>";#Newline is not clean
    }
    if(isset($args["renderLink"])&&$args["renderLink"]===true)
    {
      $renderedView.= "\nIssues: ".JIRA::getFilterURL($data["host"],$data["jql"])."";
    }
    return $renderedView;
  }
}
