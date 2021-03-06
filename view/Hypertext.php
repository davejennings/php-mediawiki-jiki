<?php
if(!defined("MEDIAWIKI"))
{
   echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
   die(1);
}
define("JIKI_VIEW_HTML_GREY","background-color:#4a6785;border-color:#4a6785;color:#FFFFFF;border-radius:3px;padding:1px 3px;");
define("JIKI_VIEW_HTML_ORANGE","background-color:#ffd351;border-color:#ffd351;color:#594300;border-radius:3px;padding:1px 3px;");
define("JIKI_VIEW_HTML_GREEN","background-color:#14892c;border-color:#14892c;color:#FFFFFF;border-radius:3px;padding:1px 3px;");
/**
 * an HTML renderer for JIRA data
 */
class Hypertext
{
  /**
   * get the rendered view in HTML format
   *
   * @param array data the array of data to be rendered
   */
  function getRenderedView(&$data,$args=array())
  {
    $renderedView = "";
    foreach($data["data"] as $issue)
    {
      $renderedView.= "<img title=\"".$issue["fields"]["issuetype"]["name"].": ".$issue["fields"]["issuetype"]["description"]."\" src=\"".$issue["fields"]["issuetype"]["iconUrl"]."\"/> ";
      $renderedView.= "<strong>{$issue["key"]}</strong> ";
      $renderedView.= "<a href=\"".JIRA::getIssueURL($data["host"],$issue["key"])."\" target=\"_BLANK\">{$issue["fields"]["summary"]}</a> ";
      $statusStyle = JIKI_VIEW_HTML_GREY;
      if(isset($issue["fields"]["status"]["statusCategory"]))#JIRA provides color
      {
        #TODO: identify how to use ids for status names or just configure this
        switch(strtolower($issue["fields"]["status"]["statusCategory"]["colorName"]))
        {
          case "green":
          {
            $statusStyle = JIKI_VIEW_HTML_GREEN;
            break;
          }
          case "yellow":
          {
            $statusStyle = JIKI_VIEW_HTML_ORANGE;
            break;
          }
          default:
          {
            $statusStyle = JIKI_VIEW_HTML_GREY;
            break;
          }
        }
        $renderedView.= "<span style=\"{$statusStyle}\">{$issue["fields"]["status"]["name"]}</span> ";
      }
      else#color not found
      {
        $renderedView.= "({$issue["fields"]["status"]["name"]}) ";
      }
      if(isset($issue["fixVersions"]))
      {
        foreach($issue["fixVersions"] as $issueFixVersion)
        {
          $renderedView.= "{$issueFixVersion["name"]} ";
          if(isset($issueFixVersion["releaseDate"])&&isset($issueFixVersion["released"])&&$issueFixVersion["released"]===true)
          {
            $renderedView.= "({$issueFixVersion["releaseDate"]}) ";
          }
        }
      }
      if(isset($args["renderDetails"])&&$args["renderDetails"]===true)
      {
        $renderedView.= "<br/><div style=\"text-indent: 20px;\">{$issue["renderedFields"][JIKI_FULL_DETAILS_FIELD]}</div>";
      }
      $renderedView.= "<br/>";
    }
    if(isset($args["renderLink"])&&$args["renderLink"]===true)
    {
      $renderedView.= "<a href =\"".JIRA::getFilterURL($data["host"],$data["jql"])."\" target=\"_BLANK\">view in JIRA</a>";
    }
    return $renderedView;
  }
}
